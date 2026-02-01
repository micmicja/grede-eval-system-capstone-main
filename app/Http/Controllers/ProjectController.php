<?php

namespace App\Http\Controllers;

use App\Models\Quiz_exam_activity;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TeacherSetting;

class ProjectController extends Controller
{
    public function show()
    {
        $teacher = Auth::user();
        $students = Student::where('teacher_id', $teacher->id)->get();
        return view('Project.Show', compact('students', 'teacher'));
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

        $projectWeight = $setting->project_weight; // ex: 15%

        // 🔹 Find student
        $student = Student::where('full_name', $validated['full_name'])
            ->where('teacher_id', $teacher->id)
            ->first();

        if (!$student) {
            return back()->with('error', 'Student not found.');
        }

        // 🔹 Compute raw percentage (0–100)
        if (!empty($validated['total_items'])) {
            $rawScore = (float) $validated['score'];
            $totalItems = (int) $validated['total_items'];
            $percentage = $totalItems > 0
                ? round(($rawScore / $totalItems) * 100, 1)
                : 0;
        } else {
            $percentage = min(100, max(0, (float) $validated['score']));
        }

        // 🔹 APPLY PROJECT WEIGHT
        $weightedProjectScore = round(($percentage * $projectWeight) / 100, 2);


        // 🔹 Save project record
        Quiz_exam_activity::create([
            'full_name' => $validated['full_name'],
            'subject' => $student->subject,
            'section' => $student->section,
            'user_id' => $teacher->id,
            'activity_type' => 'project',
            'activity_title' => $validated['activity_title'],
            'date_taken' => $validated['date_taken'],
            'score' => $percentage,                     // raw % (0–100)
            'weighted_score' => $weightedProjectScore,  // final contribution   
        ]);

        return redirect()->back()
            ->with('success', 'Project recorded successfully for ' . $validated['full_name']);
    }

    public function edit($id)
    {
        $proj = Quiz_exam_activity::findOrFail($id);
        if ($proj->user_id !== Auth::id()) abort(403);
        return view('Project.Edit', compact('proj'));
    }

    public function update(Request $request, $id)
    {
        $proj = Quiz_exam_activity::findOrFail($id);
        if ($proj->user_id !== Auth::id()) abort(403);

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

            $proj->update([
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
            $proj->update($validated);
        }

        return redirect()->route('Dashboard.teacher')->with('success', 'Project updated successfully');
    }

    public function destroy($id)
    {
        $proj = Quiz_exam_activity::findOrFail($id);
        if ($proj->user_id !== Auth::id()) abort(403);
        $fullName = $proj->full_name;
        $proj->delete();
        return redirect()->back()->with('success', 'Project deleted successfully for ' . $fullName);
    }

    public function report($studentId)
    {
        $student = Student::findOrFail($studentId);
        if ($student->teacher_id !== Auth::id()) abort(403);

        $projectRecords = Quiz_exam_activity::where('full_name', $student->full_name)
            ->where('user_id', Auth::id())
            ->where('activity_type', 'project')
            ->orderBy('date_taken', 'desc')
            ->get();

        $totalProjects = $projectRecords->count();
        $averageProjectScore = $totalProjects > 0 ? $projectRecords->avg('score') : 0;
        $highestProjectScore = $totalProjects > 0 ? $projectRecords->max('score') : 0;

        return view('Project.Report', compact('student', 'projectRecords', 'totalProjects', 'averageProjectScore', 'highestProjectScore'));
    }
}
