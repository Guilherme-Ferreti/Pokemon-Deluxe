<?php

declare(strict_types=1);

use Shared\Enums\PokemonType;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the pokemon page without errors', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user);

    visit(route('pokemon.web.show', $pokemon))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues();
});

it("shows the pokemon's name, level, boost and stats", function () {
    $user       = User::factory()->create();
    $charmeleon = Species::factory()->create(['name' => 'Charmeleon', 'types' => [PokemonType::FIRE]]);
    $pokemon    = Pokemon::factory()->for($user)->for($charmeleon)->inBox()->shiny()->create(['level' => 36, 'experience' => 36720, 'boost' => 14]);

    actingAs($user);

    visit(route('pokemon.web.show', $pokemon))
        ->assertSeeIn('@pokemon-hero__name', 'Shiny Charmeleon')
        ->assertSeeIn('@pokemon-hero__level-indicator', 'Lv 36')
        ->assertSeeIn('@pokemon-hero__boost-indicator', 'Boost +14')
        ->assertSeeIn('@pokemon-hero__experience-indicator', '720 / 1,000 XP to Lv 37')
        ->assertSeeIn('@pokemon-stats-card__hp-indicator', '238')
        ->assertSeeIn('@pokemon-stats-card__attack-indicator', 'x0.85')
        ->assertSeeIn('@pokemon-stats-card__defense-indicator', '−34%')
        ->hover('@pokemon-stats-card__hp-indicator')
        ->assertSee('Level 36: 167 · Boost +14: x1.14 · Shiny: x1.25')
        ->assertNoJavascriptErrors();
});

it('says a level 100 pokemon is at the max level', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create(['level' => 100]);

    actingAs($user);

    visit(route('pokemon.web.show', $pokemon))
        ->assertSeeIn('@pokemon-hero__experience-indicator', 'Max level');
});

it('blocks releasing a locked pokemon and removing the last party pokemon', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->locked()->create();

    actingAs($user);

    visit(route('pokemon.web.show', $pokemon))
        ->assertVisible('@pokemon-hero__locked-indicator')
        ->assertButtonDisabled('@pokemon-actions__release-button')
        ->assertButtonDisabled('@pokemon-actions__remove-from-party-button');
});
