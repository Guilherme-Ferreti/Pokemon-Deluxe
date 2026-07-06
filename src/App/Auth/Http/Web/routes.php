<?php

declare(strict_types=1);

use App\Auth\Http\Web\Controllers\LoginController;
use App\Auth\Http\Web\Controllers\RegisterController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->name('auth.login.create')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->name('auth.login.store')->middleware('guest');

Route::get('/register', [RegisterController::class, 'create'])->name('auth.register.create')->middleware('guest');
Route::post('/register', [RegisterController::class, 'store'])->name('auth.register.store')->middleware(['guest', HandlePrecognitiveRequests::class]);
