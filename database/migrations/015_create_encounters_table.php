<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Shared\Enums\Area;
use Shared\Enums\PokemonGender;
use Shared\Models\Species;
use Shared\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounters', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignIdFor(User::class)->unique()->constrained()->cascadeOnDelete();

            $table->enum('area', Arr::pluck(Area::cases(), 'value'));

            $table->foreignIdFor(Species::class)->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->boolean('is_shiny')->default(false);
            $table->enum('gender', Arr::pluck(PokemonGender::cases(), 'value'));

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
