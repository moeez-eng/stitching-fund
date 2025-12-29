<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Filament\Register\Pages\RegisterPage;



Route::get('/', function () {
    return view('welcome');
})->name('home');

// Add login route alias for Filament
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

