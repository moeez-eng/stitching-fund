<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DemoRegisterController extends Controller
{
    // SHOW DEMO REGISTER FORM
    public function show()
    {
        // Logout any existing user to ensure clean registration flow
        Auth::logout();
        
        // Redirect to registration with demo parameter
        return redirect()->route('filament.admin.auth.register', ['demo' => 'true']);
    }

    // HANDLE DEMO USER CREATION
    public function store(Request $request)
    {
        // This won't be used since we're redirecting to Filament's registration
        return redirect()->route('filament.admin.auth.register');
    }
}
