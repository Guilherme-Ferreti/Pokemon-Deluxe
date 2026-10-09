<?php

declare(strict_types=1);

use App\Pokemon\Http\Web\Controllers\BoxController;
use App\Pokemon\Http\Web\Controllers\EggIncubatorActionController;
use App\Pokemon\Http\Web\Controllers\NurseryController;
use App\Pokemon\Http\Web\Controllers\PartyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/your-pokemon')->group(function (): void {
    Route::get('/party', PartyController::class)->name('party');

    Route::get('/box', [BoxController::class, 'index'])->name('box.index');

    Route::get('/nursery', NurseryController::class)->name('nursery');

    Route::prefix('/egg-incubator/{eggIncubatorIndex}')
        ->name('egg-incubators.')
        ->controller(EggIncubatorActionController::class)
        ->whereNumber('eggIncubatorIndex')
        ->group(function (): void {
            Route::post('/place-egg', 'placeEgg')->name('place-egg');
            Route::post('/hatch-egg', 'hatchEgg')->name('hatch-egg');
            Route::post('/capture-pokemon', 'capturePokemon')->name('capture-pokemon');
        });
});
