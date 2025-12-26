<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Filament\Notifications\Notification;

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
                // Send notification using Filament's notification system
                Notification::make()
                    ->title('Your account is inactive')
                    ->body('Please contact admin')
                    ->danger()
                    ->send();
                
                // Logout the user
                Auth::logout();
                
                // Redirect to login
                return redirect()->route('filament.admin.auth.login');
            }
        }

        return $next($request);
    }
}
