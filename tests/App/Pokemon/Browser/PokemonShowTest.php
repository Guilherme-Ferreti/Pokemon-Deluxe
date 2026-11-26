<?php

declare(strict_types=1);

use Shared\Enums\PokemonType;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\SpecialItem;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

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

it('swaps a known move into a slot', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);
    $iceBeam = Move::factory()->plainDamage()->create(['name' => 'Ice Beam']);

    $pokemon->knownMoves()->attach([$tackle->id => ['slot' => 1], $iceBeam->id => ['slot' => null]]);

    actingAs($user);

    visit(route('pokemon.web.show', $pokemon))
        ->assertSeeIn('@pokemon-moves-card__slot-1-item', 'Tackle')
        ->click('@pokemon-moves-card__slot-1-swap-button')
        ->assertSeeIn('@pokemon-swap-move-dialog', 'Replace Tackle')
        ->click("@pokemon-swap-move-dialog__move-{$iceBeam->id}-button")
        ->assertSeeIn('@pokemon-moves-card__slot-1-item', 'Ice Beam')
        ->assertNoJavascriptErrors();

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $iceBeam->id, 'slot' => 1]);
});

it('reorders the active moves with the arrows', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);
    $ember   = Move::factory()->plainDamage()->create(['name' => 'Ember']);

    $pokemon->knownMoves()->attach([$tackle->id => ['slot' => 1], $ember->id => ['slot' => 2]]);

    actingAs($user);

    visit(route('pokemon.web.show', $pokemon))
        ->assertButtonDisabled('@pokemon-moves-card__slot-1-up-button')
        ->click('@pokemon-moves-card__slot-2-up-button')
        ->assertSeeIn('@pokemon-moves-card__slot-1-item', 'Ember')
        ->assertSeeIn('@pokemon-moves-card__slot-2-item', 'Tackle')
        ->assertNoJavascriptErrors();

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => 1]);
});

it('teaches a tm after confirming', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->regular()->create(['level' => 36]);
    $iceBeam = Move::factory()->plainDamage()->create(['name' => 'Ice Beam', 'tm_code' => 'TM395']);

    InventoryItem::factory()->for($user)->tm($iceBeam)->create(['quantity' => 1]);

    actingAs($user);

    visit(route('pokemon.web.show', $pokemon))
        ->click('@pokemon-teach-tm-dialog__open-button')
        ->fill('@pokemon-teach-tm-dialog__search-input', '395')
        ->click("@pokemon-teach-tm-dialog__tm-{$iceBeam->id}-button")
        ->assertSeeIn('@pokemon-teach-tm-dialog__confirmation-text', 'Teach Ice Beam to')
        ->click('@pokemon-teach-tm-dialog__teach-button')
        ->assertSee('Move learned')
        ->assertSeeIn('@pokemon-moves-card__known-move-list', 'Ice Beam')
        ->assertNoJavascriptErrors();

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $iceBeam->id]);
});

it('uses boost stones and shows the result', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->boosted(10)->create();

    InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 5]);

    actingAs($user);

    visit(route('pokemon.web.show', $pokemon))
        ->click('@pokemon-boost-dialog__open-button')
        ->assertSeeIn('@pokemon-boost-dialog__next-stone-text', '+10 → +11, always works')
        ->clear('@pokemon-boost-dialog__stone-count-input')
        ->type('@pokemon-boost-dialog__stone-count-input', '3')
        ->click('@pokemon-boost-dialog__submit-button')
        ->assertSeeIn('@pokemon-boost-dialog__new-boost-indicator', '+13')
        ->assertSeeIn('@pokemon-boost-dialog__summary-text', 'Used 3 stones: 3 worked, 0 failed. 2 stones left.')
        ->click('@pokemon-boost-dialog__done-button')
        ->assertSeeIn('@pokemon-hero__boost-indicator', 'Boost +13')
        ->assertNoJavascriptErrors();

    expect($pokemon->fresh()->boost)->toBe(13);
});
