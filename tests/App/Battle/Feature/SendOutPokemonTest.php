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
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
        'move_ids'                      => ['move-1'],
        'max_hp'                        => 218,
        'current_hp'                    => 218,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    $battle = Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 4,
            player_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'current_hp' => 0]),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: true,
            log: [],
        ),
    ]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.send-out'), ['combatantIndex' => 1])
        ->assertRedirect(route('battle.web.show'))
        ->assertSessionHasNoErrors();

    $state = $battle->fresh()?->state;

    expect($state?->player_side->active_index)->toBe(1)
        ->and($state?->is_awaiting_send_out)->toBeFalse()
        ->and($state?->log)->toEqual([new BattleLogEntry(3, BattleLogKind::EVENT, 'Go! Eevee!')]);
});

it('refuses to send out a fainted pokemon', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
        'move_ids'                      => ['move-1'],
        'max_hp'                        => 218,
        'current_hp'                    => 218,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    $battle = Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 4,
            player_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'current_hp' => 0]),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: true,
            log: [],
        ),
    ]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.send-out'), ['combatantIndex' => 0])
        ->assertRedirect(route('battle.web.show'))
        ->assertInertiaFlash('toast.type', 'warning');

    $state = $battle->fresh()?->state;

    expect($state?->player_side->active_index)->toBe(0)
        ->and($state?->is_awaiting_send_out)->toBeTrue();
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

    expect($battle->fresh()?->state->is_awaiting_send_out)->toBeFalse();
});
