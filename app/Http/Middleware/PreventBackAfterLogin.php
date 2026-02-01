<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventBackAfterLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        // If already logged in, prevent access to login page
        if (Auth::check()) {

            $user = Auth::user(); // ✔ No error
            $role = $user->role;

            switch ($role) {
                case 'admin':
                    return redirect()->route('Dashboard.admin');
                case 'teacher':
                    return redirect()->route('Dashboard.teacher');

                    case 'councilor':
                        return redirect()->route('councilorDashboard.view');
            }
        }

        return $next($request);
    }
}
