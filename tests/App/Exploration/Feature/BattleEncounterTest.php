<?php

declare(strict_types=1);

use Shared\Enums\Area;
use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonGenderRate;
use Shared\Models\Battle;
use Shared\Models\Encounter;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('exploration.web.battle'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('starts a battle against the encounter and shows the battle page', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->withBaseMoves()->create(['gender_rate' => PokemonGenderRate::EQUAL]);
    Pokemon::factory()->for($user)->inParty()->withActiveMoves()->create();
    Encounter::factory()->for($user)->for($species)->create(['area' => Area::FOREST]);

    actingAs($user)
        ->post(route('exploration.web.battle'))
        ->assertRedirect(route('battle.web.show'));

    assertDatabaseCount(Battle::class, 1);
    assertDatabaseCount(Encounter::class, 0);
    expect($user->battles()->sole())
        ->status->toBe(BattleStatus::ONGOING)
        ->state->area->toBe(Area::FOREST);
});

it('warns when there is no encounter to battle', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->inParty()->withActiveMoves()->create();
    Encounter::factory()->create(['area' => Area::FOREST]);

    actingAs($user)
        ->from(route('exploration.web.show', Area::FOREST))
        ->post(route('exploration.web.battle'))
        ->assertRedirect(route('exploration.web.show', Area::FOREST))
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(Battle::class, 0);
    assertDatabaseCount(Encounter::class, 1);
});
