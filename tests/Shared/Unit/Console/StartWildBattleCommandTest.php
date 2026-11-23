<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Shared\Enums\Area;
use Shared\Enums\PokemonGenderRate;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;

it('starts a wild battle in the local environment', function () {
    app()->detectEnvironment(fn () => 'local');

    $user    = User::factory()->create(['email' => 'ash@example.com']);
    $species = Species::factory()->withBaseMoves()->create(['slug' => 'pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING], 'gender_rate' => PokemonGenderRate::EQUAL]);
    Pokemon::factory()->for($user)->inParty()->withActiveMoves()->create();

    expect(Artisan::call('battle:start-wild', ['email' => 'ash@example.com', 'species-slug' => 'pidgey', 'level' => 7]))->toBe(0);

    $battle = Battle::query()->sole();

    expect($battle->user_id)->toBe($user->id)
        ->and($battle->state->opponentSide->activeCombatant()->speciesId)->toBe($species->id)
        ->and($battle->state->opponentSide->activeCombatant()->level)->toBe(7)
        ->and($battle->state->area)->toBe(Area::MEADOW);
});

it('refuses to run outside the local environment', function () {
    $user = User::factory()->create(['email' => 'ash@example.com']);
    Species::factory()->withBaseMoves()->create(['slug' => 'pidgey', 'gender_rate' => PokemonGenderRate::EQUAL]);
    Pokemon::factory()->for($user)->inParty()->withActiveMoves()->create();

    expect(Artisan::call('battle:start-wild', ['email' => 'ash@example.com', 'species-slug' => 'pidgey', 'level' => 7]))->toBe(1);

    assertDatabaseCount(Battle::class, 0);
});
