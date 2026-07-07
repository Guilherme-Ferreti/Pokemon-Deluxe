<?php

declare(strict_types=1);

use App\Dashboard\Http\Web\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', DashboardController::class)
    ->name('dashboard.home')
    ->middleware('auth');
