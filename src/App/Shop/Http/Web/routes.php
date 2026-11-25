<?php

declare(strict_types=1);

use App\Shop\Http\Web\Controllers\BuyItemController;
use App\Shop\Http\Web\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/shop')->group(function (): void {
    Route::get('/', [ShopController::class, 'index'])->name('index');

    Route::post('/buy', BuyItemController::class)->name('buy');
});
