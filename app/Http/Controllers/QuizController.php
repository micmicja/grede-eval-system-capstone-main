<?php

namespace App\Http\Controllers;

use App\Models\Quiz_exam_activity;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    /**
     * Show quiz recording form
     */
    public function show(Request $request)
    {
        $teacher = Auth::user();
        $students = Student::where('teacher_id', $teacher->id)->get();

        return view('Quiz.Show', compact('students', 'teacher'));
    }

    /**
     * Store quiz score for a student
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'activity_title' => 'required|string|max:255',
            'date_taken' => 'required|date',
            // number of correct answers (will be converted to percentage)
            'score' => 'required|numeric|min:0',
            'total_items' => 'required|integer|min:1|max:1000',
        ]);

        $teacher = Auth::user();
        
        // Get student details
        $student = Student::where('full_name', $validated['full_name'])
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$student) {
            return back()->with('error', 'Student not found.');
        }

        // convert raw correct answers to percentage
        $rawScore = (float) $validated['score'];
        $totalItems = (int) $validated['total_items'];
        $percentage = $totalItems > 0 ? round(($rawScore / $totalItems) * 100, 1) : 0;

        // Create quiz record (store percentage in score column)
        Quiz_exam_activity::create([
            'full_name' => $validated['full_name'],
            'subject' => $student->subject,
            'section' => $student->section,
            'user_id' => $teacher->id,
            'activity_type' => 'quiz',
            'activity_title' => $validated['activity_title'],
            'date_taken' => $validated['date_taken'],
            'score' => $percentage,
        ]);

        return redirect()->back()->with('success', 'Quiz score recorded successfully for ' . $validated['full_name']);
    }

    /**
     * Get quiz records for a student
     */
    public function report($studentId)
    {
        $student = Student::findOrFail($studentId);
        $quizRecords = Quiz_exam_activity::where('full_name', $student->full_name)
            ->where('user_id', Auth::id())
            ->where('activity_type', 'quiz')
            ->orderBy('date_taken', 'desc')
            ->get();

        $averageScore = $quizRecords->count() > 0 ? $quizRecords->avg('score') : 0;
        $highestScore = $quizRecords->count() > 0 ? $quizRecords->max('score') : 0;
        $lowestScore = $quizRecords->count() > 0 ? $quizRecords->min('score') : 0;

        return view('Quiz.Report', compact('student', 'quizRecords', 'averageScore', 'highestScore', 'lowestScore'));
    }

    /**
     * Edit quiz score
     */
    public function edit($id)
    {
        $quiz = Quiz_exam_activity::findOrFail($id);
        
        // Check authorization
        if ($quiz->user_id !== Auth::id()) {
            abort(403);
        }

        return view('Quiz.Edit', compact('quiz'));
    }

    /**
     * Update quiz score
     */
    public function update(Request $request, $id)
    {
        $quiz = Quiz_exam_activity::findOrFail($id);
        
        if ($quiz->user_id !== Auth::id()) {
            abort(403);
        }

        // Support updating either by entering percentage directly, or by providing raw score + total_items
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

            $quiz->update([
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

            $quiz->update($validated);
        }

        return redirect()->route('Dashboard.teacher')->with('success', 'Quiz score updated successfully');
    }

    /**
     * Delete quiz record
     */
    public function destroy($id)
    {
        $quiz = Quiz_exam_activity::findOrFail($id);
        
        if ($quiz->user_id !== Auth::id()) {
            abort(403);
        }

        $fullName = $quiz->full_name;
        $quiz->delete();

        return redirect()->back()->with('success', 'Quiz record deleted successfully for ' . $fullName);
    }
}
