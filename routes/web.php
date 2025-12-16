<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Filament\Register\Pages\RegisterPage;

Route::get('/', function () {
    return view('welcome');
})->name('home');

