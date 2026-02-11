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

        $response = $next($request);

        // Add headers to prevent caching of login page
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
