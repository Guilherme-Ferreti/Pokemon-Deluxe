<?php

declare(strict_types=1);

use Shared\Enums\MoveCategory;
use Shared\Enums\PokemonType;
use Shared\Models\Move;

it('describes what its tm teaches', function (array $attributes, string $description) {
    expect(Move::factory()->make($attributes)->tmDescription())->toBe($description);
})->with([
    'damage move'    => [['name' => 'Ice Beam', 'type' => PokemonType::ICE, 'category' => MoveCategory::DAMAGE, 'power' => 90], 'Teaches Ice Beam, an Ice-type move with 90 power.'],
    'consonant type' => [['name' => 'Earthquake', 'type' => PokemonType::GROUND, 'category' => MoveCategory::DAMAGE, 'power' => 100], 'Teaches Earthquake, a Ground-type move with 100 power.'],
    'status move'    => [['name' => 'Thunder Wave', 'type' => PokemonType::ELECTRIC, 'category' => MoveCategory::AILMENT, 'power' => null], 'Teaches Thunder Wave, an Electric-type status move.'],
]);
