<?php

declare(strict_types=1);

use App\Auth\Http\Web\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->name('auth.login.create')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->name('auth.login.store')->middleware('guest');
