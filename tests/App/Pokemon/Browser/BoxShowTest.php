<?php

declare(strict_types=1);

use Shared\Enums\PokemonType;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\SpecialItem;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

it('loads the pokemon page without errors', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->create();

    actingAs($user);

    visit(route('pokemon.web.box.show', $pokemon))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues();
});

it("shows the pokemon's name, level, boost and stats", function () {
    $user       = User::factory()->create();
    $charmeleon = Species::factory()->create(['name' => 'Charmeleon', 'types' => [PokemonType::FIRE]]);
    $pokemon    = Pokemon::factory()->for($user)->for($charmeleon)->inBox()->shiny()->create(['level' => 36, 'experience' => 36720, 'boost' => 14]);

    actingAs($user);

    visit(route('pokemon.web.box.show', $pokemon))
        ->assertSeeIn('@pokemon-hero__name', 'Shiny Charmeleon')
        ->assertSeeIn('@pokemon-hero__level-indicator', 'Lv 36')
        ->assertSeeIn('@pokemon-hero__boost-indicator', 'Boost +14')
        ->assertSeeIn('@pokemon-hero__experience-indicator', '720 / 1,000 XP to Lv 37')
        ->click('@pokemon-stats-dialog__open-button')
        ->wait(0.5)
        ->assertSeeIn('@pokemon-stats-dialog__hp-indicator', '238')
        ->assertSeeIn('@pokemon-stats-dialog__attack-indicator', 'x0.85')
        ->assertSeeIn('@pokemon-stats-dialog__defense-indicator', '−34%')
        ->assertSeeIn('@pokemon-stats-dialog__hp-parts-text', 'Level 36: 167 · Boost +14: x1.14 · Shiny: x1.25')
        ->assertNoJavascriptErrors();
});

it('says a level 100 pokemon is at the max level', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create(['level' => 100]);

    actingAs($user);

    visit(route('pokemon.web.box.show', $pokemon))
        ->assertSeeIn('@pokemon-hero__experience-indicator', 'Max level');
});

it('blocks releasing a locked pokemon and removing the last party pokemon', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inParty()->locked()->create();

    actingAs($user);

    visit(route('pokemon.web.box.show', $pokemon))
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

    visit(route('pokemon.web.box.show', $pokemon))
        ->assertSeeIn('@pokemon-move-1-slot', 'Tackle')
        ->click('@pokemon-move-1-slot__swap-button')
        ->assertSeeIn('@pokemon-swap-move-dialog', 'Replace Tackle')
        ->click("@pokemon-swap-move-dialog__move-{$iceBeam->id}-button")
        ->wait(1)
        ->assertSeeIn('@pokemon-move-1-slot', 'Ice Beam')
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

    visit(route('pokemon.web.box.show', $pokemon))
        ->assertButtonDisabled('@pokemon-move-1-slot__up-button')
        ->click('@pokemon-move-2-slot__up-button')
        ->wait(1)
        ->assertSeeIn('@pokemon-move-1-slot', 'Ember')
        ->assertSeeIn('@pokemon-move-2-slot', 'Tackle')
        ->assertNoJavascriptErrors();

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => 1]);
});

it('drags a known move onto a slot and a slot onto another', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle']);
    $ember   = Move::factory()->plainDamage()->create(['name' => 'Ember']);
    $iceBeam = Move::factory()->plainDamage()->create(['name' => 'Ice Beam']);

    $pokemon->knownMoves()->attach([$tackle->id => ['slot' => 1], $ember->id => ['slot' => 2], $iceBeam->id => ['slot' => null]]);

    actingAs($user);

    visit(route('pokemon.web.box.show', $pokemon))
        ->drag("@pokemon-known-move-{$iceBeam->id}-tile", '@pokemon-move-2-slot')
        ->wait(1)
        ->assertSeeIn('@pokemon-move-2-slot', 'Ice Beam')
        ->drag('@pokemon-move-1-slot', '@pokemon-move-2-slot')
        ->wait(1)
        ->assertSeeIn('@pokemon-move-1-slot', 'Ice Beam')
        ->assertSeeIn('@pokemon-move-2-slot', 'Tackle')
        ->assertNoJavascriptErrors();

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $iceBeam->id, 'slot' => 1]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $tackle->id, 'slot' => 2]);
    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $ember->id, 'slot' => null]);
});

it('sorts the known moves by power and filters them by type', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->create();
    $ember   = Move::factory()->plainDamage()->create(['name' => 'Ember', 'type' => PokemonType::FIRE, 'power' => 40]);
    $iceBeam = Move::factory()->plainDamage()->create(['name' => 'Ice Beam', 'type' => PokemonType::ICE, 'power' => 90]);
    $slash   = Move::factory()->plainDamage()->create(['name' => 'Slash', 'type' => PokemonType::NORMAL, 'power' => 70]);

    $pokemon->knownMoves()->attach([$ember->id => ['slot' => 1], $iceBeam->id => ['slot' => null], $slash->id => ['slot' => null]]);

    actingAs($user);

    $firstKnownMoveTestId = "document.querySelector('[data-test=\"pokemon-moves-card__known-move-list\"] li').dataset.test";

    visit(route('pokemon.web.box.show', $pokemon))
        ->assertScript($firstKnownMoveTestId, "pokemon-known-move-{$ember->id}-tile")
        ->click('@pokemon-moves-card__sort-power-button')
        ->wait(0.5)
        ->assertScript($firstKnownMoveTestId, "pokemon-known-move-{$iceBeam->id}-tile")
        ->click('@pokemon-moves-card__type-normal-button')
        ->wait(0.5)
        ->assertVisible("@pokemon-known-move-{$slash->id}-tile")
        ->assertMissing("@pokemon-known-move-{$ember->id}-tile")
        ->assertMissing("@pokemon-known-move-{$iceBeam->id}-tile")
        ->click('@pokemon-moves-card__type-all-button')
        ->wait(0.5)
        ->assertVisible("@pokemon-known-move-{$ember->id}-tile")
        ->hover("@pokemon-known-move-{$ember->id}-tile")
        ->wait(0.5)
        ->assertSee('Already in slot 1')
        ->assertNoJavascriptErrors();
});

it('sorts and filters the moves in the swap dialog', function () {
    $user         = User::factory()->create();
    $pokemon      = Pokemon::factory()->for($user)->inBox()->create();
    $ember        = Move::factory()->plainDamage()->create(['name' => 'Ember', 'type' => PokemonType::FIRE, 'power' => 40]);
    $iceBeam      = Move::factory()->plainDamage()->create(['name' => 'Ice Beam', 'type' => PokemonType::ICE, 'power' => 90]);
    $flamethrower = Move::factory()->plainDamage()->create(['name' => 'Flamethrower', 'type' => PokemonType::FIRE, 'power' => 90]);
    $tackle       = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $pokemon->knownMoves()->attach([$tackle->id => ['slot' => 1], $ember->id => ['slot' => null], $iceBeam->id => ['slot' => null], $flamethrower->id => ['slot' => null]]);

    actingAs($user);

    $firstMoveTestId = "document.querySelector('[data-test=\"pokemon-swap-move-dialog__move-list\"] button').dataset.test";

    visit(route('pokemon.web.box.show', $pokemon))
        ->click('@pokemon-move-1-slot__swap-button')
        ->wait(0.5)
        ->assertScript($firstMoveTestId, "pokemon-swap-move-dialog__move-{$ember->id}-button")
        ->click('@pokemon-swap-move-dialog__type-fire-button')
        ->wait(0.5)
        ->assertMissing("@pokemon-swap-move-dialog__move-{$iceBeam->id}-button")
        ->click('@pokemon-swap-move-dialog__sort-power-button')
        ->wait(0.5)
        ->assertScript($firstMoveTestId, "pokemon-swap-move-dialog__move-{$flamethrower->id}-button")
        ->click("@pokemon-swap-move-dialog__move-{$flamethrower->id}-button")
        ->wait(1)
        ->assertSeeIn('@pokemon-move-1-slot', 'Flamethrower')
        ->assertNoJavascriptErrors();
});

it('teaches a tm after confirming', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->regular()->create(['level' => 36]);
    $iceBeam = Move::factory()->plainDamage()->create(['name' => 'Ice Beam', 'tm_code' => 'TM395']);

    InventoryItem::factory()->for($user)->tm($iceBeam)->create(['quantity' => 1]);

    actingAs($user);

    visit(route('pokemon.web.box.show', $pokemon))
        ->click('@pokemon-teach-tm-dialog__open-button')
        ->wait(0.5)
        ->fill('@pokemon-teach-tm-dialog__search-input', '395')
        ->wait(0.5)
        ->click("@pokemon-teach-tm-dialog__tm-{$iceBeam->id}-button")
        ->wait(0.5)
        ->assertSeeIn('@pokemon-teach-tm-dialog__teach-button', 'Teach Ice Beam')
        ->click('@pokemon-teach-tm-dialog__teach-button')
        ->wait(1)
        ->assertSee('Move learned')
        ->assertSeeIn('@pokemon-moves-card__known-move-list', 'Ice Beam')
        ->assertNoJavascriptErrors();

    assertDatabaseHas('pokemon_move', ['pokemon_id' => $pokemon->id, 'move_id' => $iceBeam->id]);
});

it('evolves the pokemon after confirming', function () {
    $user       = User::factory()->create();
    $charmeleon = Species::factory()->create(['name' => 'Charmeleon']);
    $charizard  = Species::factory()->create(['name' => 'Charizard']);
    $waterStone = EvolutionItem::factory()->create(['name' => 'Water Stone']);
    $evolution  = SpeciesEvolution::factory()->create(['from_species_id' => $charmeleon->id, 'into_species_id' => $charizard->id, 'evolution_item_id' => null, 'min_level' => 36, 'gender' => null]);
    $blocked    = SpeciesEvolution::factory()->create(['from_species_id' => $charmeleon->id, 'into_species_id' => Species::factory()->create(['name' => 'Wartortle']), 'evolution_item_id' => $waterStone->id, 'min_level' => null, 'gender' => null]);
    $pokemon    = Pokemon::factory()->for($user)->for($charmeleon)->inBox()->regular()->create(['level' => 36]);

    actingAs($user);

    visit(route('pokemon.web.box.show', $pokemon))
        ->click('@pokemon-evolve-dialog__open-button')
        ->wait(0.5)
        ->assertSeeIn("@pokemon-evolve-dialog__option-{$evolution->id}-requirement-text", 'at level 36')
        ->assertSeeIn("@pokemon-evolve-dialog__option-{$blocked->id}-requirement-text", 'using Water Stone')
        ->click("@pokemon-evolve-dialog__option-{$evolution->id}-radio")
        ->click('@pokemon-evolve-dialog__continue-button')
        ->wait(0.5)
        ->assertSeeIn('@pokemon-evolve-dialog__confirmation', 'Evolve Charmeleon into Charizard?')
        ->click('@pokemon-evolve-dialog__evolve-button')
        ->wait(1)
        ->assertSeeIn('@pokemon-hero__name', 'Charizard')
        ->assertSee('Your Charmeleon evolved into Charizard.')
        ->assertNoJavascriptErrors();

    expect($pokemon->fresh()->species_id)->toBe($charizard->id);
});

it('explains why the pokemon cannot evolve', function () {
    $user       = User::factory()->create();
    $charmeleon = Species::factory()->create(['name' => 'Charmeleon']);
    $evolution  = SpeciesEvolution::factory()->create(['from_species_id' => $charmeleon->id, 'into_species_id' => Species::factory()->create(['name' => 'Charizard']), 'evolution_item_id' => null, 'min_level' => 36, 'gender' => null]);
    $young      = Pokemon::factory()->for($user)->for($charmeleon)->inBox()->regular()->create(['level' => 20]);
    $final      = Pokemon::factory()->for($user)->for(Species::factory()->create(['name' => 'Tauros']))->inBox()->regular()->create();

    actingAs($user);

    visit(route('pokemon.web.box.show', $young))
        ->click('@pokemon-evolve-dialog__open-button')
        ->wait(0.5)
        ->assertSeeIn('@pokemon-evolve-dialog', "Charmeleon can't evolve yet.")
        ->assertSeeIn("@pokemon-evolve-dialog__option-{$evolution->id}-requirement-text", 'at level 36')
        ->assertMissing('@pokemon-evolve-dialog__continue-button')
        ->navigate(route('pokemon.web.box.show', $final))
        ->assertAttribute('@pokemon-evolve-dialog__open-button', 'disabled', '')
        ->assertNoJavascriptErrors();
});

it('uses boost stones and shows the result', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->inBox()->boosted(10)->create();

    InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 5]);

    actingAs($user);

    visit(route('pokemon.web.box.show', $pokemon))
        ->click('@pokemon-boost-dialog__open-button')
        ->wait(0.5)
        ->assertSeeIn('@pokemon-boost-dialog__boost-indicator', '+10')
        ->assertSeeIn('@pokemon-boost-dialog__owned-indicator', '5')
        ->assertSeeIn('@pokemon-boost-dialog__failure-indicator', '0%')
        ->clear('@pokemon-boost-dialog__stone-count-input')
        ->type('@pokemon-boost-dialog__stone-count-input', '3')
        ->click('@pokemon-boost-dialog__submit-button')
        ->wait(1)
        ->assertSeeIn('@pokemon-boost-dialog__new-boost-indicator', '+13')
        ->assertSeeIn('@pokemon-boost-dialog__summary-text', 'Used 3 stones: 3 worked, 0 failed. 2 stones left.')
        ->click('@pokemon-boost-dialog__done-button')
        ->wait(0.5)
        ->assertSeeIn('@pokemon-hero__boost-indicator', 'Boost +13')
        ->assertNoJavascriptErrors();

    expect($pokemon->fresh()->boost)->toBe(13);
});
