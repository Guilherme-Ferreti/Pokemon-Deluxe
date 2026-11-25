<?php

declare(strict_types=1);

use App\Inventory\Http\Web\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('/inventory')->group(function (): void {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
});
