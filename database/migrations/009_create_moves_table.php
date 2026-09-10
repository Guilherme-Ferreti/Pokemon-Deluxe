<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Shared\Enums\Ailment;
use Shared\Enums\ElementalType;
use Shared\Enums\MoveCategory;
use Shared\Enums\MoveDamageClass;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moves', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->enum('type', Arr::pluck(ElementalType::cases(), 'value'));

            $table->enum('category', Arr::pluck(MoveCategory::cases(), 'value'));
            $table->enum('damage_class', Arr::pluck(MoveDamageClass::cases(), 'value'));

            $table->unsignedTinyInteger('power')->nullable();
            $table->unsignedTinyInteger('accuracy');
            $table->unsignedTinyInteger('pp');

            $table->enum('ailment', Arr::pluck(Ailment::cases(), 'value'))->nullable();
            $table->unsignedTinyInteger('ailment_chance')->nullable();

            $table->unsignedTinyInteger('flinch_chance')->nullable();

            $table->unsignedTinyInteger('min_hits')->nullable();
            $table->unsignedTinyInteger('max_hits')->nullable();

            $table->boolean('is_zmove')->default(false);

            $table->unsignedTinyInteger('generation');

            $table->string('slug')->unique();
            $table->string('external_id')->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moves');
    }
};
