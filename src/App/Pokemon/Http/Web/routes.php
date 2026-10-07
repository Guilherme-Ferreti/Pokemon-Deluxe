<?php

declare(strict_types=1);

use App\Pokemon\Http\Web\Controllers\BoxController;
use App\Pokemon\Http\Web\Controllers\HatchEggController;
use App\Pokemon\Http\Web\Controllers\NurseryController;
use App\Pokemon\Http\Web\Controllers\PartyController;
use Illuminate\Support\Facades\Route;

Route::get('/your-pokemon/party', PartyController::class)->name('party')->middleware('auth');

Route::get('/your-pokemon/box', [BoxController::class, 'index'])->name('box.index')->middleware('auth');

Route::get('/your-pokemon/nursery', NurseryController::class)->name('nursery')->middleware('auth');

Route::post('/egg-incubations/{eggIncubation}/hatch', HatchEggController::class)->name('egg-incubations.hatch')->middleware('auth');
