<?php

namespace App\Http\Controllers;

use App\Models\Quiz_exam_activity;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function show(Request $request)
    {
        $teacher = Auth::user();
        $students = Student::where('teacher_id', $teacher->id)->get();

        return view('Exam.Show', compact('students', 'teacher'));
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
            'activity_type' => 'exam',
            'activity_title' => $validated['activity_title'],
            'date_taken' => $validated['date_taken'],
            'score' => $percentage,
        ]);

        return redirect()->back()->with('success', 'Exam score recorded successfully for ' . $validated['full_name']);
    }

    public function edit($id)
    {
        $exam = Quiz_exam_activity::findOrFail($id);
        if ($exam->user_id !== Auth::id()) abort(403);
        return view('Exam.Edit', compact('exam'));
    }

    public function update(Request $request, $id)
    {
        $exam = Quiz_exam_activity::findOrFail($id);
        if ($exam->user_id !== Auth::id()) abort(403);

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

            $exam->update([
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
            $exam->update($validated);
        }

        return redirect()->route('Dashboard.teacher')->with('success', 'Exam score updated successfully');
    }

    public function destroy($id)
    {
        $exam = Quiz_exam_activity::findOrFail($id);
        if ($exam->user_id !== Auth::id()) abort(403);
        $fullName = $exam->full_name;
        $exam->delete();
        return redirect()->back()->with('success', 'Exam record deleted successfully for ' . $fullName);
    }

    public function report($studentId)
    {
        $student = Student::findOrFail($studentId);
        if ($student->teacher_id !== Auth::id()) abort(403);

        $examRecords = Quiz_exam_activity::where('full_name', $student->full_name)
            ->where('user_id', Auth::id())
            ->where('activity_type', 'exam')
            ->orderBy('date_taken', 'desc')
            ->get();

        $averageExamScore = $examRecords->count() > 0 ? $examRecords->avg('score') : 0;
        $highestExamScore = $examRecords->count() > 0 ? $examRecords->max('score') : 0;

        return view('Exam.Report', compact('student', 'examRecords', 'averageExamScore', 'highestExamScore'));
    }
}
