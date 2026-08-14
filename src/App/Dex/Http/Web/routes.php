<?php

declare(strict_types=1);

use App\Dex\Http\Web\Controllers\PokedexController;
use Illuminate\Support\Facades\Route;

Route::get('/pokedex', [PokedexController::class, 'index'])->name('dex.pokedex.index')->middleware('auth');
