<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shared\Models\Move;
use Shared\Models\Pokemon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokemon_move', function (Blueprint $table): void {
            $table->foreignIdFor(Pokemon::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Move::class)->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('slot')->nullable();

            $table->unique(['pokemon_id', 'move_id']);
            $table->unique(['pokemon_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon_move');
    }
};
