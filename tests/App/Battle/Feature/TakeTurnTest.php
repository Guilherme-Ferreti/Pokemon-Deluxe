<?php

declare(strict_types=1);

use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('battle.web.turn'), ['moveSlot' => 1])
        ->assertRedirectToRoute('auth.web.login.create');
});

it('takes a turn with the chosen move', function () {
    $user         = User::factory()->create();
    $pikachu      = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $pidgey       = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $thunderShock = Move::factory()->plainDamage()->create(['name' => 'Thunder Shock', 'type' => PokemonType::ELECTRIC, 'power' => 40]);
    $tackle       = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $combatant = [
        'pokemon_id'                    => null,
        'species_id'                    => $pidgey->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::FEMALE,
        'types'                         => [PokemonType::NORMAL, PokemonType::FLYING],
        'move_ids'                      => [$tackle->id],
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
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(...[
                ...$combatant,
                'pokemon_id' => 'pokemon-1',
                'species_id' => $pikachu->id,
                'gender'     => PokemonGender::MALE,
                'types'      => [PokemonType::ELECTRIC],
                'move_ids'   => [$tackle->id, $thunderShock->id],
            ])], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            is_awaiting_send_out: false,
            log: [
                new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
                new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Pikachu!'),
            ],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, true, false, 1.0, true, false, 1.0]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.turn'), ['moveSlot' => 2])
        ->assertRedirect(route('battle.web.show'))
        ->assertSessionHasNoErrors();

    $state = $battle->fresh()?->state;

    expect($state?->turn)->toBe(2)
        ->and($state?->opponent_side->activeCombatant()->current_hp)->toBe(218 - 93)
        ->and($state?->player_side->activeCombatant()->current_hp)->toBe(218 - 46)
        ->and($state?->log)->toEqual([
            new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
            new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Pikachu!'),
            new BattleLogEntry(1, BattleLogKind::MOVE, 'Pikachu used Thunder Shock!', 'Thunder Shock'),
            new BattleLogEntry(1, BattleLogKind::DETAIL, "It's super effective!"),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 93 HP damage.'),
            new BattleLogEntry(1, BattleLogKind::MOVE, 'The wild Pidgey used Tackle!', 'Tackle'),
            new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 46 HP damage.'),
        ]);
});

it('rejects an invalid move slot', function (mixed $moveSlot) {
    $user   = User::factory()->create();
    $battle = Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.turn'), ['moveSlot' => $moveSlot])
        ->assertRedirect(route('battle.web.show'))
        ->assertSessionHasErrors('moveSlot');

    expect($battle->fresh()?->state->turn)->toBe(1);
})->with([
    'missing'      => [null],
    'zero'         => [0],
    'too high'     => [5],
    'not a number' => ['first'],
]);

it('warns when there is no battle in progress', function () {
    $user = User::factory()->create();

    Battle::factory()->for($user)->create(['status' => BattleStatus::WON]);
    $otherUsersBattle = Battle::factory()->create(['status' => BattleStatus::ONGOING]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.turn'), ['moveSlot' => 1])
        ->assertRedirect(route('battle.web.show'))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($otherUsersBattle->fresh()?->state->turn)->toBe(1);
});
