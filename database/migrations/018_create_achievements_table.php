<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Shared\Enums\MissionCategory;
use Shared\Enums\MissionGoal;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('slug')->unique();

            $table->string('name');
            $table->enum('category', Arr::pluck(MissionCategory::cases(), 'value'));
            $table->enum('goal', Arr::pluck(MissionGoal::cases(), 'value'));
            $table->json('goal_parameters');
            $table->unsignedInteger('target');
            $table->json('reward');
            $table->unsignedSmallInteger('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
