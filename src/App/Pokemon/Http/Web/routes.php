<?php

declare(strict_types=1);

use App\Pokemon\Http\Web\Controllers\BoxController;
use App\Pokemon\Http\Web\Controllers\Incubator\CatchPokemonController;
use App\Pokemon\Http\Web\Controllers\Incubator\HatchEggController;
use App\Pokemon\Http\Web\Controllers\Incubator\PlaceEggController;
use App\Pokemon\Http\Web\Controllers\Incubator\PurchaseController;
use App\Pokemon\Http\Web\Controllers\NurseryController;
use App\Pokemon\Http\Web\Controllers\PartyController;
use App\Pokemon\Http\Web\Controllers\PokemonController;
use App\Pokemon\Http\Web\Controllers\ReorderActiveMovesController;
use App\Pokemon\Http\Web\Controllers\SetActiveMoveController;
use App\Pokemon\Http\Web\Controllers\TeachTmController;
use App\Pokemon\Http\Web\Controllers\UseBoostStonesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/your-pokemon')->group(function (): void {
    Route::get('/party', PartyController::class)->name('party');

    Route::get('/box', [BoxController::class, 'index'])->name('box.index');

    Route::get('/nursery', NurseryController::class)->name('nursery');

    Route::prefix('/{pokemon}')->whereUuid('pokemon')->group(function (): void {
        Route::get('/', [PokemonController::class, 'show'])->name('show');
        Route::post('/active-moves', SetActiveMoveController::class)->name('set-active-move');
        Route::post('/active-moves/order', ReorderActiveMovesController::class)->name('reorder-active-moves');
        Route::post('/teach-tm', TeachTmController::class)->name('teach-tm');
        Route::post('/boost', UseBoostStonesController::class)->name('boost');
    });

    Route::prefix('/incubator/{incubatorIndex}')
        ->name('incubators.')
        ->whereNumber('incubatorIndex')
        ->group(function (): void {
            Route::post('/purchase', PurchaseController::class)->name('purchase');
            Route::post('/place-egg', PlaceEggController::class)->name('place-egg');
            Route::post('/hatch-egg', HatchEggController::class)->name('hatch-egg');
            Route::post('/catch-pokemon', CatchPokemonController::class)->name('catch-pokemon');
        });
});
