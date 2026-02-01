<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {

        $user = $request->user(); // or use auth()->user()
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in your account.');
        }


        //userMapping
        $Roles = [
            'admin' => 'admin',
            'teacher' => 'teacher',
            'councilor' => 'councilor'
        ];

        $userRoles = $Roles[$user->role];

        if ($userRoles !== $role) {
            return redirect()->route('login')->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
