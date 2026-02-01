<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Quiz_exam_activity;
use App\Models\Student;
use App\Models\TeacherSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Show attendance form for a specific date
     */
    public function show(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        $teacher = Auth::user();

        // Get students taught by this teacher
        $students = Student::where('teacher_id', $teacher->id)->get();

        // Get existing attendance records for the date
        $attendanceRecords = Attendance::where('user_id', $teacher->id)
            ->where('date', $date)
            ->get()
            ->keyBy('full_name'); // Index by student name for quick lookup

        return view('Attendance.Show', compact('students', 'date', 'attendanceRecords', 'teacher'));
    }

    /**
     * Store attendance records for multiple students
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array', // attendance[full_name] = 1 or 0
        ]);

        $teacher = Auth::user();
        $date = $validated['date'];
        $attendanceData = $validated['attendance'];

        // Delete old records for this date and teacher (to avoid duplicates)
        Attendance::where('user_id', $teacher->id)
            ->where('date', $date)  // ← Only deletes records for THIS date
            ->delete();

        // Insert new attendance records
        foreach ($attendanceData as $fullName => $present) {
            $student = Student::where('full_name', $fullName)
                ->where('teacher_id', $teacher->id)
                ->first();

            if ($student) {
                Attendance::create([
                    'full_name' => $fullName,
                    'subject' => $student->subject,
                    'section' => $student->section,
                    'user_id' => $teacher->id,
                    'date' => $date,
                    'present' => (bool) $present,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Attendance recorded successfully for ' . $date);
    }

    /**
     * Get attendance report for a student
     */
    // public function report($studentId)
    // {
    //     $teacher = Auth::user();
    //     $student = Student::findOrFail($studentId);
    //     if ($student->teacher_id !== $teacher->id) abort(403);

    //     // 1. KUNIN ANG WEIGHTS (Base sa Dashboard mo)
    //     $settings = TeacherSetting::where('user_id', $teacher->id)->first();
    //     $w = [
    //         'attendance' => 10, // 10% fixed base sa screenshot
    //         'quiz'       => $settings->quiz_weight ?? 10,
    //         'exam'       => $settings->exam_weight ?? 25,
    //         'activity'   => $settings->activity_weight ?? 25,
    //         'project'    => $settings->project_weight ?? 15,
    //         'recitation' => $settings->recitation_weight ?? 25,
    //     ];

    //     // 2. ATTENDANCE COMPUTATION (Mula sa Attendance Table)
    //     $attendanceRecords = \App\Models\Attendance::where('full_name', $student->full_name)
    //         ->where('user_id', $teacher->id)
    //         ->get();

    //     $totalDays = $attendanceRecords->count();
    //     $presentDays = $attendanceRecords->where('present', true)->count();

    //     // Ito ang "Attendance Score" (0-100)
    //     $attendancePercentage = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;

    //     // 3. OTHER CATEGORIES (Mula sa Quiz_exam_activity Table)
    //     $allRecords = Quiz_exam_activity::where('full_name', $student->full_name)
    //         ->where('user_id', $teacher->id)
    //         ->get();

    //     $quizAvg       = $allRecords->where('activity_type', 'quiz')->avg('score') ?? 0;
    //     $examAvg       = $allRecords->where('activity_type', 'exam')->avg('score') ?? 0;
    //     $activityAvg   = $allRecords->where('activity_type', 'activity')->avg('score') ?? 0;
    //     $projectAvg    = $allRecords->where('activity_type', 'project')->avg('score') ?? 0;
    //     $recitationAvg = $allRecords->where('activity_type', 'recitation')->avg('score') ?? 0;

    //     // 4. ANG TAMANG WEIGHTED COMPUTATION
    //     // Formula: (Category Average * Category Weight) / 100
    //     $overallScore = ($attendancePercentage * ($w['attendance'] / 100)) +
    //         ($quizAvg             * ($w['quiz']       / 100)) +
    //         ($examAvg             * ($w['exam']       / 100)) +
    //         ($activityAvg         * ($w['activity']   / 100)) +
    //         ($projectAvg          * ($w['project']    / 100)) +
    //         ($recitationAvg       * ($w['recitation'] / 100));

    //     // 5. LETTER GRADE LOGIC
    //     $grade = 'F';
    //     if ($overallScore >= 95) $grade = 'A+';
    //     elseif ($overallScore >= 90) $grade = 'A';
    //     elseif ($overallScore >= 85) $grade = 'B+';
    //     elseif ($overallScore >= 80) $grade = 'B';
    //     elseif ($overallScore >= 75) $grade = 'C';
    //     elseif ($overallScore >= 70) $grade = 'D';

    //     return view('Activity.Report', compact(
    //         'student',
    //         'attendancePercentage',
    //         'totalDays',
    //         'presentDays',
    //         'quizAvg',
    //         'examAvg',
    //         'activityAvg',
    //         'projectAvg',
    //         'recitationAvg',
    //         'overallScore',
    //         'grade',
    //         'w'
    //     ));
    // }
}
