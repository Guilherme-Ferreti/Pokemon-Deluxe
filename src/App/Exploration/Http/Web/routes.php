<?php

declare(strict_types=1);

use App\Exploration\Http\Web\Controllers\ExploreController;
use Illuminate\Support\Facades\Route;

Route::get('/explore', [ExploreController::class, 'index'])->name('index')->middleware('auth');
