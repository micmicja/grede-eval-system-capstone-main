<?php

namespace App\Http\Controllers;

use App\Models\TeacherSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherSettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $settings = TeacherSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'quiz_weight' => 25,
                'exam_weight' => 25,
                'activity_weight' => 25,
                 'attendance_weight' => 10,
                'project_weight' => 15,
                'recitation_weight' => 5     
            ]
        );

        return view('Teacher.Settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'quiz_weight' => 'required|integer|min:0|max:100',
            'exam_weight' => 'required|integer|min:0|max:100',
            'activity_weight' => 'required|integer|min:0|max:100',
            'project_weight' => 'required|integer|min:0|max:100',
            'recitation_weight' => 'required|integer|min:0|max:100',
            'attendance_weight' => 'required|integer|min:0|max:100',
        ]);

        $sum = array_sum(array_values($data));
        if ($sum !== 100) {
            return back()->withErrors(['weights' => 'Total weight allocation must equal 100%.'])->withInput();
        }

        $setting = TeacherSetting::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return redirect()->route('teacher.settings')->with('success', 'Grade weights updated successfully!');
    }
}
