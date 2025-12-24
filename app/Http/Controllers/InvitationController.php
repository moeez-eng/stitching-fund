<?php

namespace App\Http\Controllers;

use App\Models\UserInvitation;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    public function accept($companySlug, $uniqueCode)
    {
        if (!$companySlug || !$uniqueCode) {
            return redirect('/admin/login')->with('error', 'Invalid invitation link');
        }

        $invitation = UserInvitation::where('unique_code', $uniqueCode)->first();

        if (!$invitation) {
            return redirect('/admin/login')->with('error', 'Invitation not found');
        }

        // Validate company slug matches
        $expectedSlug = \Illuminate\Support\Str::slug($invitation->company_name);
        if ($companySlug !== $expectedSlug) {
            return redirect('/admin/login')->with('error', 'Invalid invitation link');
        }

        if ($invitation->isAccepted()) {
            return redirect('/admin/login')->with('warning', 'This invitation has already been accepted');
        }

        if ($invitation->isExpired()) {
            return redirect('/admin/login')->with('error', 'This invitation has expired');
        }

        // Show the invitation acceptance form
        return view('filament.pages.accept-invitation', [
            'invitation' => $invitation,
            'companySlug' => $companySlug,
            'uniqueCode' => $uniqueCode
        ]);
    }

    public function store(Request $request, $companySlug, $uniqueCode)
    {
        $invitation = UserInvitation::where('unique_code', $uniqueCode)->first();

        if (!$invitation || $invitation->isAccepted() || $invitation->isExpired()) {
            return redirect('/admin/login')->with('error', 'Invalid or expired invitation');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if user already exists
        if (\App\Models\User::where('email', $invitation->email)->exists()) {
            return back()->with('error', 'An account with this email already exists');
        }

        // Create user
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'role' => $invitation->role,
            'status' => 'active',
            'invited_by' => $invitation->invited_by,
            'company_name' => $invitation->company_name,
        ]);

        // Mark invitation as accepted
        $invitation->update([
            'accepted_at' => now(),
        ]);

        // Log the user in
        Auth::login($user);

        return redirect('/admin')->with('success', 'Welcome! Your account has been created successfully.');
    }
}
