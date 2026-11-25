<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tm_rotations', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->date('date')->unique();
            $table->json('move_ids');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tm_rotations');
    }
};
