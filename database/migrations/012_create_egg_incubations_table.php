<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Shared\Enums\PokemonGender;
use Shared\Models\Species;
use Shared\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egg_incubations', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('egg_incubator_index');

            $table->foreignIdFor(Species::class)->constrained()->cascadeOnDelete();
            $table->enum('gender', Arr::pluck(PokemonGender::cases(), 'value'));
            $table->boolean('is_shiny')->default(false);

            $table->timestamp('ready_to_hatch_at');
            $table->timestamp('hatched_at')->nullable();
            $table->timestamp('captured_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'egg_incubator_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egg_incubations');
    }
};
