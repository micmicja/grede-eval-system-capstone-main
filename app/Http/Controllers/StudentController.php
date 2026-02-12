<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Quiz_exam_activity;
use App\Models\Attendance;
use App\Models\TeacherSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Store a new student observation with automated risk analysis
     */
    public function storeObservation(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'observed_behaviors' => 'nullable|array',
        ]);

        $studentId = $request->student_id;
        $teacherId = Auth::id();

        // Check if student already has an observation referred to councilor
        // Allow multiple observations for non-referred students (Low/Mid Risk)
        // But prevent duplicate referrals for High/Mid High Risk students
        $existingObservation = DB::table('student_observations')
            ->where('student_id', $studentId)
            ->where('referred_to_councilor', true)
            ->first();

        if ($existingObservation) {
            return redirect()->back()->with('error', 'This student has already been referred to the counselor. Cannot create duplicate observation.');
        }

        // Step A: Auto-Calculate Average with weighted grades
        $average = $this->calculateWeightedAverage($studentId);

        // Step B: Determine Risk Level
        $riskStatus = $this->determineRiskLevel($average);

        // Step C: The Councilor Trigger - All risk levels are referred to counselor
        $referredToCouncilor = in_array($riskStatus, ['High Risk', 'Mid High Risk', 'Mid Risk', 'Low Risk']);

        // Save the observation
        $observation = DB::table('student_observations')->insert([
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'calculated_average' => $average,
            'risk_status' => $riskStatus,
            'observed_behaviors' => json_encode($request->observed_behaviors ?? []),
            'referred_to_councilor' => $referredToCouncilor,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // TODO: Send notification to Guidance Councilor
        if ($referredToCouncilor) {
            // Placeholder for notification logic
        }

        return redirect()->back()->with('success', 'Student observation recorded successfully. Risk Status: ' . $riskStatus);
    }

    /**
     * Calculate weighted average from student's existing grades
     * Uses teacher-specific weights from teacher_settings table
     * Filters by current semester/year and teacher ID to match Overall Score calculation
     * 
     * Default Weights (if not configured):
     * - Quiz: 15%
     * - Exam: 25%
     * - Activity: 25%
     * - Project: 15%
     * - Recitation: 10%
     * - Attendance: 10%
     * Total: 100%
     */
    private function calculateWeightedAverage($studentId)
    {
        // Get student data
        $student = Student::findOrFail($studentId);
        
        // Use the student's teacher to get their grades
        // The observation creator might be different from the student's teacher
        $teacherId = $student->teacher_id;

        // Get teacher's custom weights
        $settings = TeacherSetting::where('user_id', $teacherId)->first();
        $weights = [
            'attendance' => $settings->attendance_weight ?? 10,
            'quiz'       => $settings->quiz_weight ?? 15,
            'exam'       => $settings->exam_weight ?? 25,
            'activity'   => $settings->activity_weight ?? 25,
            'project'    => $settings->project_weight ?? 15,
            'recitation' => $settings->recitation_weight ?? 10
        ];

        // Determine current semester date range (matches StudentReportController logic)
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $semester = $month <= 6 ? 1 : 2;

        if ($semester === 1) {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 6, 30)->endOfDay();
        } else {
            $start = Carbon::create($year, 7, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
        }

        // Query filters to match Overall Score calculation
        $commonQuery = [
            ['full_name', '=', $student->full_name],
            ['user_id', '=', $teacherId]
        ];

        // Get all activities for this student and teacher within the semester
        $allActivities = Quiz_exam_activity::where($commonQuery)
            ->whereBetween('date_taken', [$start->toDateString(), $end->toDateString()])
            ->get();

        $quizRecords = $allActivities->where('activity_type', 'Quiz');
        $examRecords = $allActivities->where('activity_type', 'Exam');
        $activityRecords = $allActivities->where('activity_type', 'Activity');
        $projectRecords = $allActivities->where('activity_type', 'Project');
        $recitationRecords = $allActivities->where('activity_type', 'Recitation');

        // Calculate averages
        $quizCount = $quizRecords->count();
        $examCount = $examRecords->count();
        $activityCount = $activityRecords->count();
        $projectCount = $projectRecords->count();
        $recitationCount = $recitationRecords->count();

        $quizAvg = $quizCount > 0 ? $quizRecords->avg('score') : 0;
        $examAvg = $examCount > 0 ? $examRecords->avg('score') : 0;
        $activityAvg = $activityCount > 0 ? $activityRecords->avg('score') : 0;
        $projectAvg = $projectCount > 0 ? $projectRecords->avg('score') : 0;
        $recitationAvg = $recitationCount > 0 ? $recitationRecords->avg('score') : 0;

        // Get Attendance percentage (matches StudentReportController logic)
        $attendanceRecords = Attendance::where($commonQuery)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $totalAttendance = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('present', true)->count();
        $attendancePercentage = $totalAttendance > 0 ? ($presentCount / $totalAttendance) * 100 : 0;

        // Calculate weighted average (only include components that have data)
        $numerator = 0;
        $denominator = 0;

        if ($totalAttendance > 0) {
            $numerator += ($attendancePercentage * $weights['attendance']);
            $denominator += $weights['attendance'];
        }
        if ($quizCount > 0) {
            $numerator += ($quizAvg * $weights['quiz']);
            $denominator += $weights['quiz'];
        }
        if ($examCount > 0) {
            $numerator += ($examAvg * $weights['exam']);
            $denominator += $weights['exam'];
        }
        if ($activityCount > 0) {
            $numerator += ($activityAvg * $weights['activity']);
            $denominator += $weights['activity'];
        }
        if ($projectCount > 0) {
            $numerator += ($projectAvg * $weights['project']);
            $denominator += $weights['project'];
        }
        if ($recitationCount > 0) {
            $numerator += ($recitationAvg * $weights['recitation']);
            $denominator += $weights['recitation'];
        }

        $average = $denominator > 0 ? ($numerator / $denominator) : 0;

        return round($average, 2);
    }

    /**
     * Determine risk level based on calculated average
     * New ranges:
     * - High: 59 and below
     * - Mid High: 60 to 75
     * - Mid: 76 to 89
     * - Low: 90 to 100
     */
    private function determineRiskLevel($average)
    {
        if ($average < 60) {
            return 'High Risk';
        } elseif ($average >= 60 && $average <= 75) {
            return 'Mid High Risk';
        } elseif ($average >= 76 && $average <= 89) {
            return 'Mid Risk';
        } else { // 90 - 100
            return 'Low Risk';
        }
    }
}
