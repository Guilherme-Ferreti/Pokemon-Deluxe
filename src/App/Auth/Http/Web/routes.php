<?php

declare(strict_types=1);

use App\Auth\Http\Web\Controllers\LoginController;
use App\Auth\Http\Web\Controllers\LogoutController;
use App\Auth\Http\Web\Controllers\RegistrationController;
use App\Auth\Http\Web\Middlewares\ShareAuthLayoutProps;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->name('login.create')->middleware('guest', ShareAuthLayoutProps::class);
Route::post('/login', [LoginController::class, 'store'])->name('login.store')->middleware('guest', 'throttle:login');

Route::get('/registration', [RegistrationController::class, 'create'])->name('registration.create')->middleware('guest', ShareAuthLayoutProps::class);
Route::post('/registration', [RegistrationController::class, 'store'])->name('registration.store')->middleware(['guest', 'throttle:registration', HandlePrecognitiveRequests::class]);

Route::post('/logout', LogoutController::class)->name('logout')->middleware('auth');
