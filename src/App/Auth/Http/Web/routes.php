<?php

declare(strict_types=1);

use App\Auth\Http\Web\Controllers\LoginController;
use App\Auth\Http\Web\Controllers\LogoutController;
use App\Auth\Http\Web\Controllers\RegisterController;
use App\Auth\Http\Web\Middlewares\ShareAuthLayoutProps;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->name('login.create')->middleware('guest', ShareAuthLayoutProps::class);
Route::post('/login', [LoginController::class, 'store'])->name('login.store')->middleware('guest');

Route::get('/register', [RegisterController::class, 'create'])->name('register.create')->middleware('guest', ShareAuthLayoutProps::class);
Route::post('/register', [RegisterController::class, 'store'])->name('register.store')->middleware(['guest', HandlePrecognitiveRequests::class]);

Route::post('/logout', LogoutController::class)->name('logout')->middleware('auth');
