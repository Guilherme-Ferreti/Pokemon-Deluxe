<?php

declare(strict_types=1);

use App\Battle\Http\Web\Controllers\BattleController;
use App\Battle\Http\Web\Controllers\TakeTurnController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/battle')->group(function (): void {
    Route::get('/', [BattleController::class, 'show'])->name('show');

    Route::post('/turn', TakeTurnController::class)->name('turn');
});
