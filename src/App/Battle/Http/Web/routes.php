<?php

declare(strict_types=1);

use App\Battle\Http\Web\Controllers\BattleController;
use App\Battle\Http\Web\Controllers\ForfeitBattleController;
use App\Battle\Http\Web\Controllers\SendOutPokemonController;
use App\Battle\Http\Web\Controllers\SwitchPokemonController;
use App\Battle\Http\Web\Controllers\TakeTurnController;
use App\Battle\Http\Web\Controllers\ThrowBallController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/battle')->group(function (): void {
    Route::get('/', [BattleController::class, 'show'])->name('show');

    Route::post('/turn', TakeTurnController::class)->name('turn');

    Route::post('/send-out', SendOutPokemonController::class)->name('send-out');

    Route::post('/switch', SwitchPokemonController::class)->name('switch');
    Route::post('/throw-ball', ThrowBallController::class)->name('throw-ball');
    Route::post('/forfeit', ForfeitBattleController::class)->name('forfeit');
});
