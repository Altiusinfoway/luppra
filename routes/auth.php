<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;


Route::get('/login/{lang?}', [LoginController::class, 'showLoginForm'])
                ->middleware('guest')
                ->name('login');

Route::post('/login', [LoginController::class, 'login'])
                ->middleware('guest')
                ->name('login.submit');

Route::post('/logout', [LoginController::class, 'logout'])
                ->middleware('auth')
                ->name('logout');
