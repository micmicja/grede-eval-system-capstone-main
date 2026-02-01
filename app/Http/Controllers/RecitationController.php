<?php

namespace App\Http\Controllers;

use App\Models\Quiz_exam_activity;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TeacherSetting;

class RecitationController extends Controller
{
    public function show()
    {
        $teacher = Auth::user();
        $students = Student::where('teacher_id', $teacher->id)->get();
        return view('Recitation.Show', compact('students', 'teacher'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'activity_title' => 'required|string|max:255',
            'date_taken' => 'required|date',
            'score' => 'required|numeric|min:0',
            'total_items' => 'nullable|integer|min:1|max:1000',
        ]);

        $teacher = Auth::user();

        // 🔹 Get teacher grading settings
        $setting = TeacherSetting::where('user_id', $teacher->id)->first();
        if (!$setting) {
            return back()->with('error', 'Please configure grading settings first.');
        }

        $recitationWeight = $setting->recitation_weight; // ex: 15%


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
            $percentage = min(100, max(0, (float) $validated['score']));
        }

        // APPLY PROJECT WEIGHT
        $weightedProjectScore = round(($percentage * $recitationWeight) / 100, 2);


        Quiz_exam_activity::create([
            'full_name' => $validated['full_name'],
            'subject' => $student->subject,
            'section' => $student->section,
            'user_id' => $teacher->id,
            'activity_type' => 'recitation',
            'activity_title' => $validated['activity_title'],
            'date_taken' => $validated['date_taken'],
            'score' => $percentage,
             'weighted_score' =>  $weightedProjectScore
        ]);

        return redirect()->back()->with('success', 'Recitation recorded successfully for ' . $validated['full_name']);
    }

    public function edit($id)
    {
        $rec = Quiz_exam_activity::findOrFail($id);
        if ($rec->user_id !== Auth::id()) abort(403);
        return view('Recitation.Edit', compact('rec'));
    }

    public function update(Request $request, $id)
    {
        $rec = Quiz_exam_activity::findOrFail($id);
        if ($rec->user_id !== Auth::id()) abort(403);

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

            $rec->update([
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
            $rec->update($validated);
        }

        return redirect()->route('Dashboard.teacher')->with('success', 'Recitation updated successfully');
    }

    public function destroy($id)
    {
        $rec = Quiz_exam_activity::findOrFail($id);
        if ($rec->user_id !== Auth::id()) abort(403);
        $fullName = $rec->full_name;
        $rec->delete();
        return redirect()->back()->with('success', 'Recitation deleted successfully for ' . $fullName);
    }

    public function report($studentId)
    {
        $student = Student::findOrFail($studentId);
        if ($student->teacher_id !== Auth::id()) abort(403);

        $recitationRecords = Quiz_exam_activity::where('full_name', $student->full_name)
            ->where('user_id', Auth::id())
            ->where('activity_type', 'recitation')
            ->orderBy('date_taken', 'desc')
            ->get();

        $totalRecitations = $recitationRecords->count();
        $averageRecitationScore = $totalRecitations > 0 ? $recitationRecords->avg('score') : 0;
        $highestRecitationScore = $totalRecitations > 0 ? $recitationRecords->max('score') : 0;

        return view('Recitation.Report', compact('student', 'recitationRecords', 'totalRecitations', 'averageRecitationScore', 'highestRecitationScore'));
    }
}
