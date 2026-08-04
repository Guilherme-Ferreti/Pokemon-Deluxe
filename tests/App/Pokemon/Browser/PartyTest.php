<?php

declare(strict_types=1);

use Shared\Models\Pokedex;
use Shared\Models\Pokemon;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the party page without errors', function () {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->count(3)->inParty()->create();

    actingAs($user);

    visit(route('web.pokemon.party'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@page-title', 'Your Party');
});

it('shows party pokemon', function () {
    $user    = User::factory()->create();
    $pikachu = Pokedex::factory()->create(['name' => 'Pikachu']);

    Pokemon::factory()->for($user)->for($pikachu)->inParty()->create(['level' => 10]);

    actingAs($user);

    visit(route('web.pokemon.party'))
        ->assertSee('Pikachu')
        ->assertSee('Lv. 10');
});
