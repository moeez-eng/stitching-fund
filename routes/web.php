<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Filament\Register\Pages\RegisterPage;



Route::get('/', function () {
    return view('welcome');
})->name('home');

// Route to admin landing page
Route::get('/admin', function () {
    if (Auth::check()) {
        return redirect('/admin/dashboard');
    }
    return redirect('/admin/dashboard');
})->name('admin.home');

// Add login route alias for Filament
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

