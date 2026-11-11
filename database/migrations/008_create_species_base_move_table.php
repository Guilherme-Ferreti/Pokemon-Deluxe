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
        Schema::create('species_base_move', function (Blueprint $table): void {
            $table->foreignIdFor(Species::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Move::class)->constrained()->cascadeOnDelete();

            $table->unique(['species_id', 'move_id']);
            $table->index('move_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('species_base_move');
    }
};
