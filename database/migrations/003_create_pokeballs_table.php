<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokeballs', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('slug')->unique();

            $table->string('name');
            $table->text('description');
            $table->string('sprite')->nullable();
            $table->string('type');
            $table->decimal('catch_rate', 5, 2)->unsigned();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokeballs');
    }
};
