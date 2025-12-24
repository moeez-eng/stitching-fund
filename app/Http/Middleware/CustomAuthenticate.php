<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Filament\Http\Middleware\Authenticate;

class CustomAuthenticate extends Authenticate
{
    protected function redirectTo($request): ?string
    {
        // Don't redirect from login/register pages - allow access
      if ($request->path() === 'admin/login' || $request->path() === 'admin/register') {
            return null;
        }
        
        // For all other unauthenticated requests, redirect to login
        return route('filament.admin.auth.login');
    }

    protected function authenticate($request, array $guards): void
    {
        // Debug: Log the request path
        Log::info('CustomAuthenticate called for: ' . $request->path());
        
        // Allow access to registration and login pages without authentication
        if ($request->is('admin/*') && ($request->is('admin/login') || $request->is('admin/register'))) {
            Log::info('Skipping authentication for: ' . $request->path());
            return; // Skip authentication entirely
        }
        
        Log::info('Running parent authentication for: ' . $request->path());
        // For all other routes, use parent authentication
        parent::authenticate($request, $guards);
    }
}
