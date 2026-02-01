<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //

        $query = User::where('role', 'teacher');

        // Filter by name
        if ($request->filled('search_name')) {
            $query->where('full_name', 'like', '%' . $request->search_name . '%');
        }

        // Filter by section
        if ($request->filled('search_section')) {
            $query->where('section', 'like', '%' . $request->search_section . '%');
        }

        $teacher_list = $query->get();
        $countedTeacher = $teacher_list->count();

        // Counselor query
        $counselorQuery = User::where('role', 'councilor');

        // Filter by counselor name
        if ($request->filled('search_counselor_name')) {
            $counselorQuery->where('full_name', 'like', '%' . $request->search_counselor_name . '%');
        }

        $counselor_list = $counselorQuery->get();
        $countedCounselor = $counselor_list->count();

        return view('Admin.Dashboard', [
            'teacher_list' => $teacher_list,
            'countedTeacher' => $countedTeacher,
            'counselor_list' => $counselor_list,
            'countedCounselor' => $countedCounselor,
            'search_name' => $request->search_name,
            'search_section' => $request->search_section,
            'search_counselor_name' => $request->search_counselor_name,
        ]);
    }

    /**


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //

    }

    /**
     * Show the form for editing the specified teacher.
     */
    public function edit(string $id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);

        return view('Admin.EditTeacher', compact('teacher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $teacher->id,
            'section' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
        ]);

        $teacher->full_name = $data['full_name'];
        $teacher->username = $data['username'];
        $teacher->section = $data['section'] ?? $teacher->section;
        $teacher->subject = $data['subject'] ?? $teacher->subject;

        if (!empty($data['password'])) {
            $teacher->password = Hash::make($data['password']);
        }

        $teacher->save();

        return redirect()->route('Dashboard.admin')->with('success', 'Teacher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);
        $teacher->delete();
        return redirect()->route('Dashboard.admin')->with('success', 'Teacher deleted successfully.');
    }

    /**
     * Show the form for creating a new counselor.
     */
    public function createCounselor()
    {
        return view('Admin.CreateCounselor');
    }

    /**
     * Store a newly created counselor in storage.
     */
    public function storeCounselor(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => 'councilor',
        ]);

        return redirect()->route('Dashboard.admin', ['tab' => 'counselors'])->with('success', 'Counselor created successfully.');
    }

    /**
     * Show the form for editing the specified counselor.
     */
    public function editCounselor(string $id)
    {
        $counselor = User::where('role', 'councilor')->findOrFail($id);
        return view('Admin.EditCounselor', compact('counselor'));
    }

    /**
     * Update the specified counselor in storage.
     */
    public function updateCounselor(Request $request, string $id)
    {
        $counselor = User::where('role', 'councilor')->findOrFail($id);

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $counselor->id,
            'password' => 'nullable|string|min:8',
        ]);

        $counselor->full_name = $data['full_name'];
        $counselor->username = $data['username'];

        if (!empty($data['password'])) {
            $counselor->password = Hash::make($data['password']);
        }

        $counselor->save();

        return redirect()->route('Dashboard.admin', ['tab' => 'counselors'])->with('success', 'Counselor updated successfully.');
    }

    /**
     * Remove the specified counselor from storage.
     */
    public function destroyCounselor(string $id)
    {
        $counselor = User::where('role', 'councilor')->findOrFail($id);
        $counselor->delete();
        return redirect()->route('Dashboard.admin', ['tab' => 'counselors'])->with('success', 'Counselor deleted successfully.');
    }
}
