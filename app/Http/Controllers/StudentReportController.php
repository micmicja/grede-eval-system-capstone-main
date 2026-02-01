<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\EvalutionComment;
use App\Models\Quiz_exam_activity;
use App\Models\TeacherSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentReportController extends Controller
{
    /**
     * Show combined report for a student (attendance + quiz + exam)
     */
    public function show(Request $request, $studentId)
    {
        $student = Student::findOrFail($studentId);

        // Check authorization - student must belong to logged-in teacher
        if ($student->teacher_id !== Auth::id()) {
            abort(403);
        }

        // Allow filtering by semester and year via query params
        $semester = (int) $request->query('semester', 0); // 0 = all
        $year = (int) $request->query('year', Carbon::now()->year);

        // Compute date range
        if ($semester === 1) {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 6, 30)->endOfDay();
        } elseif ($semester === 2) {
            $start = Carbon::create($year, 7, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
        } else {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
        }

        // Get records within range
        $commonQuery = [
            ['full_name', '=', $student->full_name],
            ['user_id', '=', Auth::id()]
        ];

        $attendanceRecords = Attendance::where($commonQuery)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date', 'desc')->get();

        $allActivities = Quiz_exam_activity::where($commonQuery)
            ->whereBetween('date_taken', [$start->toDateString(), $end->toDateString()])
            ->get();

        // Grouping records by activity_type (case-insensitive)
        $quizRecords = $allActivities->filter(function($item) {
            return strtolower($item->activity_type) === 'quiz';
        })->sortByDesc('date_taken');
        $examRecords = $allActivities->filter(function($item) {
            return strtolower($item->activity_type) === 'exam';
        })->sortByDesc('date_taken');
        $activityRecords = $allActivities->filter(function($item) {
            return strtolower($item->activity_type) === 'activity';
        })->sortByDesc('date_taken');
        $projectRecords = $allActivities->filter(function($item) {
            return strtolower($item->activity_type) === 'project';
        })->sortByDesc('date_taken');
        $recitationRecords = $allActivities->filter(function($item) {
            return strtolower($item->activity_type) === 'recitation';
        })->sortByDesc('date_taken');

        // Calculate Stats
        $totalAttendanceDays = $attendanceRecords->count();
        $presentDays = $attendanceRecords->where('present', true)->count();
        $attendancePercentage = $totalAttendanceDays > 0 ? ($presentDays / $totalAttendanceDays) * 100 : 0;

        $totalQuizzes = $quizRecords->count();
        $averageQuizScore = $totalQuizzes > 0 ? $quizRecords->avg('weighted_score') : 0;
        $highestQuizScore = $totalQuizzes > 0 ? $quizRecords->max('weighted_score') : 0;

        $totalExams = $examRecords->count();
        $averageExamScore = $totalExams > 0 ? $examRecords->avg('weighted_score') : 0;
        $highestExamScore = $totalExams > 0 ? $examRecords->max('weighted_score') : 0;

        $totalActivities = $activityRecords->count();
        $averageActivityScore = $totalActivities > 0 ? $activityRecords->avg('weighted_score') : 0;
        $highestActivityScore = $totalActivities > 0 ? $activityRecords->max('weighted_score') : 0;
        $lowestActivityScore = $totalActivities > 0 ? $activityRecords->min('weighted_score') : 0;

        $totalProjects = $projectRecords->count();
        $averageProjectScore = $totalProjects > 0 ? $projectRecords->avg('weighted_score') : 0;
        $highestProjectScore = $totalProjects > 0 ? $projectRecords->max('weighted_score') : 0;
        $lowestProjectScore = $totalProjects > 0 ? $projectRecords->min('weighted_score') : 0;

        $totalRecitations = $recitationRecords->count();
        $averageRecitationScore = $totalRecitations > 0 ? $recitationRecords->avg('weighted_score') : 0;
        $highestRecitationScore = $totalRecitations > 0 ? $recitationRecords->max('weighted_score') : 0;
        $lowestRecitationScore = $totalRecitations > 0 ? $recitationRecords->min('weighted_score') : 0;

        // Weighting Logic
        $settings = TeacherSetting::where('user_id', Auth::id())->first();
        
        // FIX: Inayos ang syntax mula sa screenshot mo ($settings-attendance_weight naging $settings->attendance_weight)
        $weights = [
            'attendance' => $settings->attendance_weight ?? 10, 
            'quiz'       => $settings->quiz_weight ?? 15,
            'exam'       => $settings->exam_weight ?? 25,
            'activity'   => $settings->activity_weight ?? 25,
            'project'    => $settings->project_weight ?? 15,
            'recitation' => $settings->recitation_weight ?? 10
        ];

        $numerator = 0;
        $denominator = 0;

        // Computation Logic (Isama ang Attendance)
        if ($totalAttendanceDays > 0) {
            $numerator += ($attendancePercentage * $weights['attendance']);
            $denominator += $weights['attendance'];
        }
        if ($totalQuizzes > 0) {
            $numerator += ($averageQuizScore * $weights['quiz']);
            $denominator += $weights['quiz'];
        }
        if ($totalExams > 0) {
            $numerator += ($averageExamScore * $weights['exam']);
            $denominator += $weights['exam'];
        }
        if ($totalActivities > 0) {
            $numerator += ($averageActivityScore * $weights['activity']);
            $denominator += $weights['activity'];
        }
        if ($totalProjects > 0) {
            $numerator += ($averageProjectScore * $weights['project']);
            $denominator += $weights['project'];
        }
        if ($totalRecitations > 0) {
            $numerator += ($averageRecitationScore * $weights['recitation']);
            $denominator += $weights['recitation'];
        }

        $overallWeighted = $denominator > 0 ? ($numerator / $denominator) : 0;

        return view('Report.StudentReport', compact(
            'student', 'attendanceRecords', 'quizRecords', 'examRecords', 'activityRecords', 'projectRecords', 'recitationRecords',
            'totalAttendanceDays', 'presentDays', 'attendancePercentage',
            'totalQuizzes', 'averageQuizScore', 'highestQuizScore',
            'totalExams', 'averageExamScore', 'highestExamScore',
            'totalActivities', 'averageActivityScore', 'highestActivityScore', 'lowestActivityScore',
            'totalProjects', 'averageProjectScore', 'highestProjectScore', 'lowestProjectScore',
            'totalRecitations', 'averageRecitationScore', 'highestRecitationScore', 'lowestRecitationScore',
            'settings', 'overallWeighted', 'semester', 'year', 'start', 'end'
        ));
    }

    public function ShowFlagFormPage($id)
    {
        $student = Student::findOrFail($id);
        if ($student->teacher_id !== Auth::id()) {
            return redirect()->route('login');
        }
        // Compute automatic urgency based on current semester/year performance
        $overall = $this->computeWeightedOverallForStudent($student);
        $autoUrgency = $this->mapUrgencyFromScore($overall);

        return view('Report.FlagStudent', compact('student', 'overall', 'autoUrgency'));
    }

    public function storeFlag(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'category' => 'nullable|string', // category may be removed from the form
            'comments' => 'required|min:5',
        ]);

        $student = Student::findOrFail($request->student_id);
        if ($student->teacher_id !== Auth::id()) {
            abort(403);
        }

        // compute urgency automatically (do NOT allow teacher override)
        $overall = $this->computeWeightedOverallForStudent($student);
        $computedUrgency = $this->mapUrgencyFromScore($overall);
        $urgency = $computedUrgency;

        EvalutionComment::create([
            'student_id' => $request->student_id,
            'teacher_id' => Auth::id(),
            'urgency' => $urgency,
            'comments' => $request->comments,
            'category' => $request->input('category', 'Unspecified'),
        ]);

        return redirect()->route('Dashboard.teacher')->with('success', 'Student has been flagged for counseling.');
    }

    /**
     * Compute weighted overall score for a student for the current semester/year.
     */
    private function computeWeightedOverallForStudent(Student $student, $semester = null, $year = null)
    {
        $year = $year ?? Carbon::now()->year;
        if ($semester === null) {
            $month = Carbon::now()->month;
            $semester = $month <= 6 ? 1 : 2;
        }

        if ($semester === 1) {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 6, 30)->endOfDay();
        } elseif ($semester === 2) {
            $start = Carbon::create($year, 7, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
        } else {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
        }

        $commonQuery = [
            ['full_name', '=', $student->full_name],
            ['user_id', '=', Auth::id()]
        ];

        $attendanceRecords = Attendance::where($commonQuery)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date', 'desc')->get();

        $allActivities = Quiz_exam_activity::where($commonQuery)
            ->whereBetween('date_taken', [$start->toDateString(), $end->toDateString()])
            ->get();

        $quizRecords = $allActivities->where('activity_type', 'quiz');
        $examRecords = $allActivities->where('activity_type', 'exam');
        $activityRecords = $allActivities->where('activity_type', 'activity');
        $projectRecords = $allActivities->where('activity_type', 'project');
        $recitationRecords = $allActivities->where('activity_type', 'recitation');

        $totalAttendanceDays = $attendanceRecords->count();
        $presentDays = $attendanceRecords->where('present', true)->count();
        $attendancePercentage = $totalAttendanceDays > 0 ? ($presentDays / $totalAttendanceDays) * 100 : 0;

        $totalQuizzes = $quizRecords->count();
        $averageQuizScore = $totalQuizzes > 0 ? $quizRecords->avg('score') : 0;

        $totalExams = $examRecords->count();
        $averageExamScore = $totalExams > 0 ? $examRecords->avg('score') : 0;

        $totalActivities = $activityRecords->count();
        $averageActivityScore = $totalActivities > 0 ? $activityRecords->avg('score') : 0;

        $totalProjects = $projectRecords->count();
        $averageProjectScore = $totalProjects > 0 ? $projectRecords->avg('score') : 0;

        $totalRecitations = $recitationRecords->count();
        $averageRecitationScore = $totalRecitations > 0 ? $recitationRecords->avg('score') : 0;

        $settings = TeacherSetting::where('user_id', Auth::id())->first();
        $weights = [
            'attendance' => $settings->attendance_weight ?? 10,
            'quiz'       => $settings->quiz_weight ?? 15,
            'exam'       => $settings->exam_weight ?? 25,
            'activity'   => $settings->activity_weight ?? 25,
            'project'    => $settings->project_weight ?? 15,
            'recitation' => $settings->recitation_weight ?? 10
        ];

        $numerator = 0;
        $denominator = 0;

        if ($totalAttendanceDays > 0) {
            $numerator += ($attendancePercentage * $weights['attendance']);
            $denominator += $weights['attendance'];
        }
        if ($totalQuizzes > 0) {
            $numerator += ($averageQuizScore * $weights['quiz']);
            $denominator += $weights['quiz'];
        }
        if ($totalExams > 0) {
            $numerator += ($averageExamScore * $weights['exam']);
            $denominator += $weights['exam'];
        }
        if ($totalActivities > 0) {
            $numerator += ($averageActivityScore * $weights['activity']);
            $denominator += $weights['activity'];
        }
        if ($totalProjects > 0) {
            $numerator += ($averageProjectScore * $weights['project']);
            $denominator += $weights['project'];
        }
        if ($totalRecitations > 0) {
            $numerator += ($averageRecitationScore * $weights['recitation']);
            $denominator += $weights['recitation'];
        }

        return $denominator > 0 ? ($numerator / $denominator) : 0;
    }

    private function mapUrgencyFromScore($score)
    {
        // Higher urgency for lower scores
        if ($score >= 90) {
            return 'low';
        } elseif ($score >= 70) {
            return 'mid';
        }

        return 'high';
    }
}