<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\Move;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('dex.web.attackdex.index'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the attackdex page with expected structure', function () {
    $user = User::factory()->create();

    $moveA = Move::factory()->create(['name' => 'Ember']);
    $moveB = Move::factory()->create(['name' => 'Tackle']);

    actingAs($user);

    get(route('dex.web.attackdex.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dex/Attackdex/Index')
            ->has('activeFilters')
            ->has('filterOptions', fn (Assert $page) => $page->hasAll(['types', 'ailments', 'traits']))
            ->has('moves.data', 2)
            ->where('moves.data.0.id', $moveA->id)
            ->where('moves.data.0.name', $moveA->name)
            ->where('moves.data.0.type', $moveA->type)
            ->where('moves.data.0.category', $moveA->category)
            ->where('moves.data.0.power', $moveA->power)
            ->where('moves.data.0.accuracy', $moveA->accuracy)
            ->where('moves.data.0.ailment', $moveA->ailment)
            ->where('moves.data.0.ailmentChance', $moveA->ailment_chance)
            ->where('moves.data.0.flinchChance', $moveA->flinch_chance)
            ->where('moves.data.0.isZMove', $moveA->is_z_move)
            ->where('moves.data.0.isDynamaxMove', $moveA->is_dynamax_move)
            ->where('moves.data.1.id', $moveB->id)
            ->where('moves.data.1.name', $moveB->name)
            ->where('moves.data.1.type', $moveB->type)
            ->where('moves.data.1.category', $moveB->category)
            ->where('moves.data.1.power', $moveB->power)
            ->where('moves.data.1.accuracy', $moveB->accuracy)
            ->where('moves.data.1.ailment', $moveB->ailment)
            ->where('moves.data.1.ailmentChance', $moveB->ailment_chance)
            ->where('moves.data.1.flinchChance', $moveB->flinch_chance)
            ->where('moves.data.1.isZMove', $moveB->is_z_move)
            ->where('moves.data.1.isDynamaxMove', $moveB->is_dynamax_move)
            ->where('moves.total', 2)
            ->where('moves.perPage', 50)
            ->where('moves.lastPage', 1)
        );
});

it('filters moves by search query', function () {
    $user = User::factory()->create();

    Move::factory()->create(['name' => 'Tackle']);
    Move::factory()->create(['name' => 'Ember']);

    actingAs($user)
        ->get(route('dex.web.attackdex.index', ['search' => 'Tackle']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('moves.data', 1)
            ->where('moves.data.0.name', 'Tackle')
        );
});
