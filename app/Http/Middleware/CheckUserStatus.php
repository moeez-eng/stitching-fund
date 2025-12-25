<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user is inactive
            if ($user->status === 'inactive') {
                // Logout the user
                Auth::logout();
                
                // Invalidate session
                $request->session()->invalidate();
                
                // Regenerate CSRF token
                $request->session()->regenerateToken();
                
                // Redirect to login with message
                return redirect()->route('filament.auth.login')
                    ->with('status', 'you are inactive please superadmin sa rabta kary');
            }
        }

        return $next($request);
    }
}
