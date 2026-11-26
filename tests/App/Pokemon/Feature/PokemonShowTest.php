<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\MoveCategory;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\EvolutionItem;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\Pokemon;
use Shared\Models\SpecialItem;
use Shared\Models\Species;
use Shared\Models\SpeciesEvolution;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('pokemon.web.show', Pokemon::factory()->create()))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the pokemon page with expected structure', function () {
    $user       = User::factory()->create(['name' => 'Ash']);
    $charmeleon = Species::factory()->create(['name' => 'Charmeleon', 'types' => [PokemonType::FIRE]]);
    $pokemon    = Pokemon::factory()->for($user)->for($charmeleon)->inParty()->shiny()->male()->create([
        'original_user_id' => $user->id,
        'level'            => 36,
        'experience'       => 36720,
        'boost'            => 14,
        'pokeball_id'      => Pokeball::factory()->create(['name' => 'Ultra Ball', 'sprite' => 'https://example.com/ultra-ball.png']),
        'caught_at'        => '2026-11-03 10:00:00',
    ]);

    actingAs($user)
        ->get(route('pokemon.web.show', $pokemon))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pokemon/pages/Pokemon/Show/index')
            ->where('pokemon.id', $pokemon->id)
            ->where('pokemon.name', 'Shiny Charmeleon')
            ->where('pokemon.frontSprite', $pokemon->getDisplayFrontSprite())
            ->where('pokemon.species.name', 'Charmeleon')
            ->where('pokemon.species.types', [PokemonType::FIRE->value])
            ->where('pokemon.gender', PokemonGender::MALE->value)
            ->where('pokemon.isShiny', true)
            ->where('pokemon.level', 36)
            ->where('pokemon.boost', 14)
            ->where('pokemon.experience', ['current' => 720, 'needed' => 1000])
            ->where('pokemon.pokeball', ['name' => 'Ultra Ball', 'sprite' => 'https://example.com/ultra-ball.png'])
            ->where('pokemon.caughtAt', $pokemon->caught_at->toIso8601String())
            ->where('pokemon.isLocked', false)
            ->where('pokemon.inParty', true)
            ->where('pokemon.partySize', 1)
            ->where('stats.hp', ['value' => $pokemon->getStats()->hp, 'base' => 167, 'boostMultiplier' => 1.14, 'shinyMultiplier' => 1.25])
            ->where('stats.attack.boostMultiplier', 1.14)
            ->where('stats.attack.shinyMultiplier', 1.25)
            ->where('stats.damageTaken.boostMultiplier', 0.88)
            ->where('stats.damageTaken.shinyMultiplier', 0.75)
        );
});

it('has the active moves by slot and every known move', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->create();
    $ember   = Move::factory()->plainDamage()->create(['name' => 'Ember', 'type' => PokemonType::FIRE, 'power' => 40]);
    $slash   = Move::factory()->plainDamage()->create(['name' => 'Slash', 'type' => PokemonType::NORMAL, 'power' => 70]);
    $growl   = Move::factory()->create(['is_z_move' => false, 'is_dynamax_move' => false, 'name' => 'Growl', 'type' => PokemonType::NORMAL, 'power' => null, 'category' => MoveCategory::AILMENT]);

    $pokemon->knownMoves()->attach([$slash->id => ['slot' => 2], $ember->id => ['slot' => 1], $growl->id => ['slot' => null]]);

    actingAs($user)
        ->get(route('pokemon.web.show', $pokemon))
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeMoves', [
                ['slot' => 1, 'move' => ['id' => $ember->id, 'name' => 'Ember', 'type' => 'fire', 'power' => 40, 'category' => 'damage', 'isZMove' => false, 'isDynamaxMove' => false]],
                ['slot' => 2, 'move' => ['id' => $slash->id, 'name' => 'Slash', 'type' => 'normal', 'power' => 70, 'category' => 'damage', 'isZMove' => false, 'isDynamaxMove' => false]],
            ])
            ->has('knownMoves', 3)
            ->where('knownMoves.0.name', 'Ember')
            ->where('knownMoves.1', ['id' => $growl->id, 'name' => 'Growl', 'type' => 'normal', 'power' => null, 'category' => MoveCategory::AILMENT->value, 'isZMove' => false, 'isDynamaxMove' => false, 'slot' => null])
        );
});

it('has each evolution option with its unmet requirement', function () {
    $user       = User::factory()->create();
    $poliwhirl  = Species::factory()->create(['name' => 'Poliwhirl']);
    $waterStone = EvolutionItem::factory()->create(['name' => 'Water Stone']);
    $kingsRock  = EvolutionItem::factory()->create(['name' => "King's Rock"]);
    $poliwrath  = SpeciesEvolution::factory()->create(['from_species_id' => $poliwhirl->id, 'into_species_id' => Species::factory()->create(['name' => 'Poliwrath']), 'evolution_item_id' => $waterStone->id, 'min_level' => null, 'gender' => null]);
    $politoed   = SpeciesEvolution::factory()->create(['from_species_id' => $poliwhirl->id, 'into_species_id' => Species::factory()->create(['name' => 'Politoed']), 'evolution_item_id' => $kingsRock->id, 'min_level' => null, 'gender' => null]);
    $pokemon    = Pokemon::factory()->for($user)->for($poliwhirl)->create(['level' => 30]);

    InventoryItem::factory()->for($user)->evolutionItem($waterStone)->create();

    actingAs($user)
        ->get(route('pokemon.web.show', $pokemon))
        ->assertInertia(fn (Assert $page) => $page
            ->has('evolutionOptions', 2)
            ->where('evolutionOptions.0.id', $politoed->id)
            ->where('evolutionOptions.0.species.name', 'Politoed')
            ->where('evolutionOptions.0.isAllowed', false)
            ->where('evolutionOptions.0.unmetRequirement', "Needs a King's Rock")
            ->where('evolutionOptions.0.evolutionItem.isOwned', false)
            ->where('evolutionOptions.1.id', $poliwrath->id)
            ->where('evolutionOptions.1.species.name', 'Poliwrath')
            ->where('evolutionOptions.1.isAllowed', true)
            ->where('evolutionOptions.1.unmetRequirement', null)
            ->where('evolutionOptions.1.evolutionItem.isOwned', true)
        );
});

it('has the owned tms and boost stones', function () {
    $user       = User::factory()->create();
    $pokemon    = Pokemon::factory()->for($user)->boosted(60)->create();
    $iceBeam    = Move::factory()->plainDamage()->create(['tm_code' => 'TM395', 'name' => 'Ice Beam']);
    $earthquake = Move::factory()->plainDamage()->create(['tm_code' => 'TM047', 'name' => 'Earthquake']);
    $zMove      = Move::factory()->plainDamage()->create(['tm_code' => 'TM900', 'is_z_move' => true]);

    $pokemon->knownMoves()->attach($iceBeam->id, ['slot' => null]);

    InventoryItem::factory()->for($user)->tm($iceBeam)->create(['quantity' => 2]);
    InventoryItem::factory()->for($user)->tm($earthquake)->create(['quantity' => 1]);
    InventoryItem::factory()->for($user)->tm($zMove)->create(['quantity' => 1]);
    InventoryItem::factory()->for($user)->specialItem(SpecialItem::factory()->boostStone()->create())->create(['quantity' => 37]);

    actingAs($user)
        ->get(route('pokemon.web.show', $pokemon))
        ->assertInertia(fn (Assert $page) => $page
            ->has('tms', 3)
            ->where('tms.0.tmName', 'TM 047 Earthquake')
            ->where('tms.0.isKnown', false)
            ->where('tms.1.tmName', 'TM 395 Ice Beam')
            ->where('tms.1.quantity', 2)
            ->where('tms.1.isKnown', true)
            ->where('tms.2.needsMaxLevel', true)
            ->where('boostStones', ['quantity' => 37, 'failureChance' => 41, 'maxBoost' => 100, 'safeUntil' => 20])
        );
});

it('has no next level or next boost at the max', function () {
    $user    = User::factory()->create();
    $pokemon = Pokemon::factory()->for($user)->boosted(100)->create(['level' => 100]);

    actingAs($user)
        ->get(route('pokemon.web.show', $pokemon))
        ->assertInertia(fn (Assert $page) => $page
            ->where('pokemon.experience', null)
            ->where('boostStones', ['quantity' => 0, 'failureChance' => null, 'maxBoost' => 100, 'safeUntil' => 20])
        );
});

it("gives 404 for another player's pokemon", function () {
    actingAs(User::factory()->create())
        ->get(route('pokemon.web.show', Pokemon::factory()->create()))
        ->assertNotFound();
});
