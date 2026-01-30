<?php

namespace App\Services\Demo;

use Illuminate\Support\Facades\Auth;

class DemoGuard
{
    public static function blockIfDemo(): void
    {
        if (Auth::user()?->is_demo) {
            abort(403, 'This action is disabled in demo mode.');
        }
    }
}
