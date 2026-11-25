<?php

declare(strict_types=1);

use App\Pokemon\Actions\SetPokemonLockAction;
use Shared\Models\Pokemon;

it('locks a pokemon', function () {
    $pokemon = Pokemon::factory()->create();

    new SetPokemonLockAction()->handle($pokemon, true);

    expect($pokemon->fresh()->is_locked)->toBeTrue();
});

it('unlocks a pokemon', function () {
    $pokemon = Pokemon::factory()->locked()->create();

    new SetPokemonLockAction()->handle($pokemon, false);

    expect($pokemon->fresh()->is_locked)->toBeFalse();
});

it('starts unlocked', function () {
    expect(Pokemon::factory()->create()->fresh()->is_locked)->toBeFalse();
});
