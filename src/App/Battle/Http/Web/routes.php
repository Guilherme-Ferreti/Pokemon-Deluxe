<?php

declare(strict_types=1);

use App\Battle\Http\Web\Controllers\BattleController;
use Illuminate\Support\Facades\Route;

Route::get('/battle', [BattleController::class, 'show'])->name('show')->middleware('auth');
