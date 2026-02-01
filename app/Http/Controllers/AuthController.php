<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //

    public function createTeacherForm()
    {
        //
        return view('Auth.CreateTeacher');
    }

    public function showLoginForm()
    {
        //
        return view('Auth.Login');
    }


    public function processLogin(Request $request)
    {
        // validate the request
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);


        // $userIp = $request->ip(); // Kinukuha ang IP ng user

        // dd($userIp);


        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            switch (Auth::user()->role) {
                case 'admin':
                    return redirect()->route('Dashboard.admin');
                case 'teacher':
                    return redirect()->route('Dashboard.teacher');

                    case 'councilor':
                          return redirect()->route('councilorDashboard.view');
                default:
                    Auth::logout();
                    return redirect()->route('login')->with('error', 'Your account role is not recognized.');
            }

        } else {
            return  back()->with('error', 'The provided credentials do not match our records.');
        }
    }


    //  logout function
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
