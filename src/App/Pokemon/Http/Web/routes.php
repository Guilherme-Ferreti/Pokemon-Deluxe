<?php

declare(strict_types=1);

use App\Pokemon\Http\Web\Controllers\BoxController;
use App\Pokemon\Http\Web\Controllers\Incubator\CapturePokemonController;
use App\Pokemon\Http\Web\Controllers\Incubator\HatchEggController;
use App\Pokemon\Http\Web\Controllers\Incubator\PlaceEggController;
use App\Pokemon\Http\Web\Controllers\Incubator\PurchaseController;
use App\Pokemon\Http\Web\Controllers\NurseryController;
use App\Pokemon\Http\Web\Controllers\PartyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/your-pokemon')->group(function (): void {
    Route::get('/party', PartyController::class)->name('party');

    Route::get('/box', [BoxController::class, 'index'])->name('box.index');

    Route::get('/nursery', NurseryController::class)->name('nursery');

    Route::prefix('/incubator/{incubatorIndex}')
        ->name('incubators.')
        ->whereNumber('incubatorIndex')
        ->group(function (): void {
            Route::post('/purchase', PurchaseController::class)->name('purchase');
            Route::post('/place-egg', PlaceEggController::class)->name('place-egg');
            Route::post('/hatch-egg', HatchEggController::class)->name('hatch-egg');
            Route::post('/capture-pokemon', CapturePokemonController::class)->name('capture-pokemon');
        });
});
