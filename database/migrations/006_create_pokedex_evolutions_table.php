<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shared\Models\EvolutionItem;
use Shared\Models\Pokedex;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokedex_evolutions', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignIdFor(Pokedex::class, 'from_pokedex_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Pokedex::class, 'to_pokedex_id')->constrained()->cascadeOnDelete();

            $table->foreignIdFor(EvolutionItem::class)->nullable()->constrained();
            $table->unsignedTinyInteger('min_level')->default(1)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokedex_evolutions');
    }
};
