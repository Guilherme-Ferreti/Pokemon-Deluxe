<?php

declare(strict_types=1);

use App\Dex\Http\Api\Controllers\SpeciesAutocompleteController;
use Illuminate\Support\Facades\Route;

Route::get('/dex/api/species/autocomplete', SpeciesAutocompleteController::class)->name('species.autocomplete')->middleware('auth', 'throttle:autocomplete');
