<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Shared\Enums\InventoryItemType;
use Shared\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();

            $table->enum('item_type', Arr::pluck(InventoryItemType::cases(), 'value'));
            $table->uuid('item_id');

            $table->unsignedInteger('quantity')->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'item_type', 'item_id']);
            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
