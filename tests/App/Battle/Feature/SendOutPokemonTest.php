<?php

declare(strict_types=1);

use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('battle.web.send-out'), ['combatantIndex' => 1])
        ->assertRedirectToRoute('auth.web.login.create');
});

it('sends out the chosen pokemon', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);

    $combatant = [
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => ['move-1'],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 4,
            playerSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'currentHp' => 0]),
                new Combatant(...[...$combatant, 'pokemonId' => 'pokemon-2', 'speciesId' => $eevee->id, 'hasEnteredField' => false]),
            ], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => null])], activeIndex: 0),
            isAwaitingSendOut: true,
            log: [],
        ),
    ]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.send-out'), ['combatantIndex' => 1])
        ->assertRedirect(route('battle.web.show'))
        ->assertSessionHasNoErrors();

    $state = $battle->fresh()?->state;

    expect($state?->playerSide->activeIndex)->toBe(1)
        ->and($state?->isAwaitingSendOut)->toBeFalse()
        ->and($state?->log)->toEqual([new BattleLogEntry(3, BattleLogKind::EVENT, 'Go! Eevee!')]);
});

it('refuses to send out a fainted pokemon', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);

    $combatant = [
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => ['move-1'],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    $battle = Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 4,
            playerSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'currentHp' => 0]),
                new Combatant(...[...$combatant, 'pokemonId' => 'pokemon-2', 'speciesId' => $eevee->id, 'hasEnteredField' => false]),
            ], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => null])], activeIndex: 0),
            isAwaitingSendOut: true,
            log: [],
        ),
    ]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.send-out'), ['combatantIndex' => 0])
        ->assertRedirect(route('battle.web.show'))
        ->assertInertiaFlash('toast.type', 'warning');

    $state = $battle->fresh()?->state;

    expect($state?->playerSide->activeIndex)->toBe(0)
        ->and($state?->isAwaitingSendOut)->toBeTrue();
});

it('rejects an invalid combatant index', function (mixed $combatantIndex) {
    $user = User::factory()->create();

    Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.send-out'), ['combatantIndex' => $combatantIndex])
        ->assertRedirect(route('battle.web.show'))
        ->assertSessionHasErrors('combatantIndex');
})->with([
    'missing'      => [null],
    'negative'     => [-1],
    'not a number' => ['first'],
]);

it('warns when no pokemon needs to be sent out', function () {
    $user   = User::factory()->create();
    $battle = Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.send-out'), ['combatantIndex' => 0])
        ->assertRedirect(route('battle.web.show'))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($battle->fresh()?->state->isAwaitingSendOut)->toBeFalse();
});
