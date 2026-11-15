<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('battles', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();

            $table->enum('type', Arr::pluck(BattleType::cases(), 'value'));
            $table->enum('status', Arr::pluck(BattleStatus::cases(), 'value'));
            $table->json('state');

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('battles');
    }
};
