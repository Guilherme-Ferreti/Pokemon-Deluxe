<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Shared\Enums\MissionDifficulty;
use Shared\Enums\MissionGoal;
use Shared\Enums\MissionKind;
use Shared\Models\Achievement;
use Shared\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_missions', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->enum('kind', Arr::pluck(MissionKind::cases(), 'value'));
            $table->foreignIdFor(Achievement::class)->nullable()->constrained()->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->enum('difficulty', Arr::pluck(MissionDifficulty::cases(), 'value'))->nullable();

            $table->enum('goal', Arr::pluck(MissionGoal::cases(), 'value'));
            $table->json('goal_parameters');
            $table->unsignedInteger('target');
            $table->unsignedInteger('progress')->default(0);
            $table->json('reward');

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('claimed_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'achievement_id']);
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_missions');
    }
};
