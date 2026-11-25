<?php

declare(strict_types=1);

use Shared\Enums\Area;
use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonGenderRate;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Encounter;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('exploration.web.explore', Area::FOREST))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('rolls a new encounter in the area and shows the area page', function () {
    $user = User::factory()->create();
    Encounter::factory()->for($user)->create(['area' => Area::COAST, 'species_id' => Species::factory()->create(['types' => [PokemonType::WATER]])]);
    $caterpie = Species::factory()->create([
        'types'           => [PokemonType::BUG],
        'evolution_stage' => 1,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'gender_rate'     => PokemonGenderRate::EQUAL,
        'wild_min_level'  => 2,
        'wild_max_level'  => 12,
    ]);

    actingAs($user)
        ->post(route('exploration.web.explore', Area::FOREST))
        ->assertRedirect(route('exploration.web.show', Area::FOREST));

    assertDatabaseCount(Encounter::class, 1);
    assertDatabaseHas(Encounter::class, ['user_id' => $user->id, 'area' => Area::FOREST->value, 'species_id' => $caterpie->id]);
});

it('warns when a battle is in progress', function () {
    $user = User::factory()->create();
    Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);

    actingAs($user)
        ->from(route('exploration.web.show', Area::FOREST))
        ->post(route('exploration.web.explore', Area::FOREST))
        ->assertRedirect(route('exploration.web.show', Area::FOREST))
        ->assertInertiaFlash('toast.type', 'warning');

    assertDatabaseCount(Encounter::class, 0);
});
