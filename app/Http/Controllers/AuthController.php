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

        // Check if user has exceeded maximum login attempts
        $maxAttempts = 3;
        $decayMinutes = 5; // Lock out for 5 minutes after 3 failed attempts
        
        $key = 'login_attempts_' . $request->ip();
        $attempts = session($key, 0);
        $lockoutTime = session($key . '_lockout_time');
        
        // Check if user is currently locked out
        if ($lockoutTime && now()->timestamp < $lockoutTime) {
            $remainingTime = ceil(($lockoutTime - now()->timestamp) / 60);
            return back()->with('error', "Too many login attempts. Please try again in {$remainingTime} minute(s).");
        }
        
        // Reset lockout if time has passed
        if ($lockoutTime && now()->timestamp >= $lockoutTime) {
            session()->forget([$key, $key . '_lockout_time']);
            $attempts = 0;
        }

        if (Auth::attempt($credentials)) {
            // Reset login attempts on successful login
            session()->forget([$key, $key . '_lockout_time']);
            
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
            // Increment failed attempts
            $attempts++;
            session([$key => $attempts]);
            
            $remainingAttempts = $maxAttempts - $attempts;
            
            if ($attempts >= $maxAttempts) {
                // Lock out the user
                $lockoutTime = now()->addMinutes($decayMinutes)->timestamp;
                session([$key . '_lockout_time' => $lockoutTime]);
                
                return back()->with('error', "Too many failed login attempts. Your account has been locked for {$decayMinutes} minutes.");
            }
            
            return back()->with('error', "The provided credentials do not match our records. {$remainingAttempts} attempt(s) remaining.");
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
