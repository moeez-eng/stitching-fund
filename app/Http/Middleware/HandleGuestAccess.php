<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HandleGuestAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   
   public function handle(Request $request, Closure $next): Response
    {
        // Allow guest access to registration page
        if ($request->is('admin/register') || $request->is('admin/login')) {
            return $next($request);
        }
        
        // For all other admin pages, require authentication
        if ($request->is('admin/*') && !Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }
        
        return $next($request);
    }
}
