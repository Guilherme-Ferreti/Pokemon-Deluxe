<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Shared\Enums\PokemonGender;
use Shared\Models\Pokeball;
use Shared\Models\Species;
use Shared\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokemon', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'original_user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Species::class)->constrained();

            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedInteger('experience')->default(0);
            $table->unsignedTinyInteger('boost')->default(0);

            $table->boolean('in_party')->default(false);
            $table->boolean('is_shiny')->default(false);

            $table->enum('gender', Arr::pluck(PokemonGender::cases(), 'value'));

            $table->foreignIdFor(Pokeball::class, 'pokeball_id')->constrained();

            $table->timestamp('caught_at');

            $table->timestamps();

            $table->index(['user_id', 'in_party']);
            $table->index(['user_id', 'species_id', 'is_shiny']);
            $table->index(['species_id', 'is_shiny']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon');
    }
};
