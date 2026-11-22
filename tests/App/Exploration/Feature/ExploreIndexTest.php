<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('exploration.web.index'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the explore page with expected structure', function () {
    actingAs(User::factory()->create());

    get(route('exploration.web.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Exploration/pages/Explore/Index/index')
            ->has('areas', 9)
            ->where('areas.0.slug', 'meadow')
            ->where('areas.0.label', 'Meadow')
            ->where('areas.0.types', [PokemonType::NORMAL->value, PokemonType::FAIRY->value])
            ->where('areas.8.slug', 'ancient-ruins')
            ->where('areas.8.label', 'Ancient Ruins')
            ->where('areas.8.types', [PokemonType::PSYCHIC->value, PokemonType::POISON->value])
            ->where('isBattleInProgress', false)
        );
});

it('tells the player a battle of theirs is in progress', function () {
    $user = User::factory()->create();

    Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);

    actingAs($user);

    get(route('exploration.web.index'))
        ->assertInertia(fn (Assert $page) => $page->where('isBattleInProgress', true));
});

it('ignores ended battles and other players\' battles', function () {
    $user = User::factory()->create();

    Battle::factory()->for($user)->create(['status' => BattleStatus::WON]);
    Battle::factory()->create(['status' => BattleStatus::ONGOING]);

    actingAs($user);

    get(route('exploration.web.index'))
        ->assertInertia(fn (Assert $page) => $page->where('isBattleInProgress', false));
});
