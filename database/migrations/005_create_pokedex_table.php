<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokedex', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('name');

            $table->string('primary_type');
            $table->string('secondary_type')->nullable();

            $table->unsignedSmallInteger('national_dex_entry_number');
            $table->string('genus');
            $table->string('text_entry');

            $table->json('sprites');

            $table->boolean('is_baby')->default(false);
            $table->boolean('is_legendary')->default(false);
            $table->boolean('is_mythical')->default(false);
            $table->boolean('is_mega')->default(false);
            $table->boolean('is_gmax')->default(false);

            $table->unsignedTinyInteger('generation');
            $table->unsignedTinyInteger('capture_rate')->nullable();

            $table->string('internal_id')->unique();
            $table->string('external_id');

            $table->timestamps();

            $table->index('national_dex_entry_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokedex');
    }
};
