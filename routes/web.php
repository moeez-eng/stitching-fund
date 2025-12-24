<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Filament\Register\Pages\RegisterPage;

Route::get('/test', function () {
    return 'Simple test route is working!';
});

Route::get('/', function () {
    return redirect('/admin');
})->name('home');

// Route::get('/accept-invitation/{companySlug}/{uniqueCode}', [App\Http\Controllers\InvitationController::class, 'accept'])->name('accept.invitation');
// Route::post('/accept-invitation/{companySlug}/{uniqueCode}', [App\Http\Controllers\InvitationController::class, 'store'])->name('accept.invitation.store');

