<?php

declare(strict_types=1);

use App\Exploration\Http\Web\Controllers\BattleEncounterController;
use App\Exploration\Http\Web\Controllers\ExploreAreaController;
use App\Exploration\Http\Web\Controllers\ExploreController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/explore')->group(function (): void {
    Route::get('/', [ExploreController::class, 'index'])->name('index');

    Route::post('/battle', BattleEncounterController::class)->name('battle');

    Route::get('/{area}', [ExploreController::class, 'show'])->name('show');
    Route::post('/{area}', ExploreAreaController::class)->name('explore');
});
