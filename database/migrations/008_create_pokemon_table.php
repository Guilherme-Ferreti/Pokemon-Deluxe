<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Shared\Models\Pokeball;
use Shared\Models\Pokedex;
use Shared\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokemon', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Pokedex::class)->constrained();

            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedInteger('experience')->default(0);
            $table->json('moves_ids')->default('[]');
            $table->boolean('in_party')->default(false);
            $table->boolean('is_shiny')->default(false);
            $table->foreignIdFor(Pokeball::class, 'pokeball_id')->constrained();
            $table->timestamp('captured_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon');
    }
};
