<?php

declare(strict_types=1);

use Shared\Enums\Area;
use Shared\Enums\PokemonType;

it('puts every pokemon type in exactly one area', function (PokemonType $type) {
    $areas = array_filter(Area::cases(), fn (Area $area) => in_array($type, $area->types(), true));

    expect($areas)->toHaveCount(1);
})->with(PokemonType::cases());

it('gives every area two different types', function (Area $area) {
    [$firstType, $secondType] = $area->types();

    expect($firstType)->not->toBe($secondType);
})->with(Area::cases());
