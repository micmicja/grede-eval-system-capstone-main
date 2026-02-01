<?php

namespace App\Http\Controllers;

use App\Models\Quiz_exam_activity;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TeacherSetting;

class ActivityController extends Controller
{
    public function show(Request $request)
    {
        $teacher = Auth::user();
        $students = Student::where('teacher_id', $teacher->id)->get();

        return view('Activity.Show', compact('students', 'teacher'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'activity_title' => 'required|string|max:255',
            'date_taken' => 'required|date',
            // support raw correct answers + total_items or percentage
            'score' => 'required|numeric|min:0',
            'total_items' => 'nullable|integer|min:1|max:1000',
        ]);

        //      Get the Activity percentage na naset ni teacher

        $teacher = Auth::user();
        $student = Student::where('full_name', $validated['full_name'])
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$student) {
            return back()->with('error', 'Student not found.');
        }

        if (!empty($validated['total_items'])) {
            $rawScore = (float) $validated['score'];
            $totalItems = (int) $validated['total_items'];
            $percentage = $totalItems > 0 ? round(($rawScore / $totalItems) * 100, 1) : 0;
        } else {
            // assume percentage provided
            $percentage = min(100, max(0, (float) $validated['score']));
        }

        Quiz_exam_activity::create([
            'full_name' => $validated['full_name'],
            'subject' => $student->subject,
            'section' => $student->section,
            'user_id' => $teacher->id,
            'activity_type' => 'activity',
            'activity_title' => $validated['activity_title'],
            'date_taken' => $validated['date_taken'],
            'score' => $percentage,
        ]);

        return redirect()->back()->with('success', 'Activity recorded successfully for ' . $validated['full_name']);
    }

    public function edit($id)
    {
        $activity = Quiz_exam_activity::findOrFail($id);
        if ($activity->user_id !== Auth::id()) abort(403);
        return view('Activity.Edit', compact('activity'));
    }

    public function update(Request $request, $id)
    {
        $activity = Quiz_exam_activity::findOrFail($id);
        if ($activity->user_id !== Auth::id()) abort(403);

        if ($request->filled('total_items')) {
            $validated = $request->validate([
                'activity_title' => 'required|string|max:255',
                'date_taken' => 'required|date',
                'score' => 'required|numeric|min:0',
                'total_items' => 'required|integer|min:1|max:1000',
            ]);

            $rawScore = (float) $validated['score'];
            $totalItems = (int) $validated['total_items'];
            $percentage = $totalItems > 0 ? round(($rawScore / $totalItems) * 100, 1) : 0;

            $activity->update([
                'activity_title' => $validated['activity_title'],
                'date_taken' => $validated['date_taken'],
                'score' => $percentage,
            ]);
        } else {
            $validated = $request->validate([
                'activity_title' => 'required|string|max:255',
                'date_taken' => 'required|date',
                'score' => 'required|numeric|min:0|max:100',
            ]);
            $activity->update($validated);
        }

        return redirect()->route('Dashboard.teacher')->with('success', 'Activity updated successfully');
    }

    public function destroy($id)
    {
        $activity = Quiz_exam_activity::findOrFail($id);
        if ($activity->user_id !== Auth::id()) abort(403);
        $fullName = $activity->full_name;
        $activity->delete();
        return redirect()->back()->with('success', 'Activity deleted successfully for ' . $fullName);
    }
// public function report($studentId)
// {
//     $teacher = Auth::user();
//     $student = Student::findOrFail($studentId);
//     if ($student->teacher_id !== $teacher->id) abort(403);

//     // 1. Kunin ang lahat ng settings ni teacher
//     $settings = TeacherSetting::where('user_id', $teacher->id)->first();
    
//     // Siguraduhin na may default weights kung wala pang settings
//     $w = [
//         'quiz' => $settings->quiz_weight ?? 25,
//         'exam' => $settings->exam_weight ?? 25,
//         'activity' => $settings->activity_weight ?? 25,
//         'project' => $settings->project_weight ?? 15,
//         'recitation' => $settings->recitation_weight ?? 10,
//     ];

//     // 2. Kunin ang lahat ng records ng student
//     $allRecords = Quiz_exam_activity::where('full_name', $student->full_name)
//         ->where('user_id', $teacher->id)
//         ->get();

//     // 3. I-compute ang average bawat category (Raw Average 0-100)
//     $quizAvg = $allRecords->where('activity_type', 'quiz')->avg('score') ?? 0;
//     $examAvg = $allRecords->where('activity_type', 'exam')->avg('score') ?? 0;
//     $activityAvg = $allRecords->where('activity_type', 'activity')->avg('score') ?? 0;
//     $projectAvg = $allRecords->where('activity_type', 'project')->avg('score') ?? 0;
//     $recitationAvg = $allRecords->where('activity_type', 'recitation')->avg('score') ?? 0;

//     // 4. TAMA NA COMPUTATION: Weighted Overall Score
//     // Formula: (Average * Weight) / 100
//     $overallScore = ($quizAvg * ($w['quiz'] / 100)) +
//                     ($examAvg * ($w['exam'] / 100)) +
//                     ($activityAvg * ($w['activity'] / 100)) +
//                     ($projectAvg * ($w['project'] / 100)) +
//                     ($recitationAvg * ($w['recitation'] / 100));

//     // 5. I-determine ang Letter Grade
//     $grade = 'F';
//     if ($overallScore >= 95) $grade = 'A+';
//     elseif ($overallScore >= 90) $grade = 'A';
//     elseif ($overallScore >= 85) $grade = 'B+';
//     elseif ($overallScore >= 80) $grade = 'B';
//     elseif ($overallScore >= 75) $grade = 'C';

//     return view('Activity.Report', [
//         'student' => $student,
//         'activityRecords' => $allRecords->where('activity_type', 'activity'),
//         'averageActivityScore' => $activityAvg,
//         'overallScore' => round($overallScore, 2),
//         'letterGrade' => $grade,
//         'weights' => $w
//     ]);
// }
}
