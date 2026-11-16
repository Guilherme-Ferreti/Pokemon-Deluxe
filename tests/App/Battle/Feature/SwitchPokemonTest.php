<?php

declare(strict_types=1);

use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('redirects guests to login', function () {
    post(route('battle.web.switch'), ['combatantIndex' => 1])
        ->assertRedirectToRoute('auth.web.login.create');
});

it('switches to the chosen pokemon', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu', 'types' => [PokemonType::ELECTRIC]]);
    $eevee   = Species::factory()->create(['name' => 'Eevee', 'types' => [PokemonType::NORMAL]]);
    $pidgey  = Species::factory()->create(['name' => 'Pidgey', 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::ELECTRIC],
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
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'types' => [PokemonType::NORMAL], 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'pokemon_id' => null, 'species_id' => $pidgey->id, 'types' => [PokemonType::NORMAL, PokemonType::FLYING]]),
            ], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
        ),
    ]);

    RandomHelper::fake([$tackle->id, true, false, 1.0]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.switch'), ['combatantIndex' => 1])
        ->assertRedirect(route('battle.web.show'))
        ->assertSessionHasNoErrors();

    $state = $battle->fresh()?->state;

    expect($state?->turn)->toBe(3)
        ->and($state?->player_side->active_index)->toBe(1)
        ->and($state?->log)->toBe(['Come back, Pikachu!', 'Go! Eevee!', 'The wild Pidgey used Tackle!']);
});

it('rejects an invalid combatant index', function (mixed $combatantIndex) {
    $user = User::factory()->create();

    Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.switch'), ['combatantIndex' => $combatantIndex])
        ->assertRedirect(route('battle.web.show'))
        ->assertSessionHasErrors('combatantIndex');
})->with([
    'missing'      => [null],
    'negative'     => [-1],
    'not a number' => ['first'],
]);

it('warns when the battle is awaiting a send-out', function () {
    $user   = User::factory()->create();
    $battle = Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);
    $battle->update(['state' => $battle->state->withAwaitingSendOut(true)]);

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.switch'), ['combatantIndex' => 1])
        ->assertRedirect(route('battle.web.show'))
        ->assertInertiaFlash('toast.type', 'warning');

    expect($battle->fresh()?->state->turn)->toBe($battle->state->turn);
});

it('warns when there is no ongoing battle', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->from(route('battle.web.show'))
        ->post(route('battle.web.switch'), ['combatantIndex' => 1])
        ->assertRedirect(route('battle.web.show'))
        ->assertInertiaFlash('toast.type', 'warning');
});
