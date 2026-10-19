<?php

declare(strict_types=1);

use App\Pokemon\Http\Web\Controllers\BoxController;
use App\Pokemon\Http\Web\Controllers\EggIncubator\CapturePokemonController;
use App\Pokemon\Http\Web\Controllers\EggIncubator\HatchEggController;
use App\Pokemon\Http\Web\Controllers\EggIncubator\PlaceEggController;
use App\Pokemon\Http\Web\Controllers\EggIncubator\PurchaseController;
use App\Pokemon\Http\Web\Controllers\NurseryController;
use App\Pokemon\Http\Web\Controllers\PartyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/your-pokemon')->group(function (): void {
    Route::get('/party', PartyController::class)->name('party');

    Route::get('/box', [BoxController::class, 'index'])->name('box.index');

    Route::get('/nursery', NurseryController::class)->name('nursery');

    Route::prefix('/egg-incubator/{eggIncubatorIndex}')
        ->name('egg-incubators.')
        ->whereNumber('eggIncubatorIndex')
        ->group(function (): void {
            Route::post('/purchase', PurchaseController::class)->name('purchase');
            Route::post('/place-egg', PlaceEggController::class)->name('place-egg');
            Route::post('/hatch-egg', HatchEggController::class)->name('hatch-egg');
            Route::post('/capture-pokemon', CapturePokemonController::class)->name('capture-pokemon');
        });
});
