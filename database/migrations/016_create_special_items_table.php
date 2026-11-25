<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Shared\Enums\SpecialItemCategory;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('slug')->unique();

            $table->string('name');
            $table->text('description');
            $table->string('sprite')->nullable();
            $table->enum('category', Arr::pluck(SpecialItemCategory::cases(), 'value'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_items');
    }
};
