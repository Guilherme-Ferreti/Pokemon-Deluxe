<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Shared\Enums\PokemonGender;
use Shared\Models\EvolutionItem;
use Shared\Models\Species;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species_evolutions', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignIdFor(Species::class, 'from_species_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Species::class, 'into_species_id')->constrained()->cascadeOnDelete();

            $table->foreignIdFor(EvolutionItem::class)->nullable()->constrained();
            $table->unsignedTinyInteger('min_level')->nullable();
            $table->enum('gender', Arr::pluck(PokemonGender::cases(), 'value'))->nullable();

            $table->unique(['from_species_id', 'into_species_id']);
            $table->index('into_species_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('species_evolutions');
    }
};
