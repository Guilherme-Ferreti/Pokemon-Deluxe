<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shared\Models\Move;
use Shared\Models\Species;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species_learnable_moves', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignIdFor(Species::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Move::class)->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('level_learn')->nullable();
            $table->boolean('is_base_move')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('species_learnable_moves');
    }
};
