<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('slug')->unique();

            $table->string('name');
            $table->json('types');

            $table->unsignedSmallInteger('pokedex_number');
            $table->string('genus');
            $table->string('text_entry');

            $table->json('sprites');
            $table->json('portraits');

            $table->boolean('is_baby')->default(false);
            $table->boolean('is_legendary')->default(false);
            $table->boolean('is_mythical')->default(false);
            $table->boolean('is_mega')->default(false);
            $table->boolean('is_gmax')->default(false);

            $table->unsignedTinyInteger('generation');
            $table->unsignedTinyInteger('catch_rate')->nullable();
            $table->tinyInteger('gender_rate');
            $table->boolean('egg_obtainable')->default(false);

            $table->index('name');
            $table->index('pokedex_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('species');
    }
};
