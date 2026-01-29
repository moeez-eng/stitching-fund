<?php

namespace App\Services\Demo;

use Illuminate\Support\Facades\Auth;

class DemoGuard
{
    public static function deny(string $message = 'Action disabled in demo mode.')
    {
        if (Auth::check() && Auth::user()->is_demo) {
            abort(403, $message);
        }
    }
}
