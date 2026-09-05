<?php

declare(strict_types=1);

use App\Pokemon\Http\Web\Controllers\BoxController;
use App\Pokemon\Http\Web\Controllers\PartyController;
use Illuminate\Support\Facades\Route;

Route::get('/your-pokemon/party', PartyController::class)->name('party')->middleware('auth');

Route::get('/your-pokemon/box', [BoxController::class, 'index'])->name('box.index')->middleware('auth');
