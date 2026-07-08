<?php

declare(strict_types=1);

use App\Pokemon\Http\Web\Controllers\PokemonController;
use Illuminate\Support\Facades\Route;

Route::get('/your-pokemon', [PokemonController::class, 'index'])->name('pokemon.index')->middleware('auth');
