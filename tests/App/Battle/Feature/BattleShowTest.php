<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\Ailment;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('battle.web.show'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('loads the battle page with expected structure', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);
    $pidgey  = Species::factory()->create(['name' => 'Pidgey']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);
    $growl   = Move::factory()->plainDamage()->create(['name' => 'Growl', 'type' => PokemonType::NORMAL, 'power' => null]);

    $combatant = [
        'pokemon_id'                    => null,
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
        'move_ids'                      => [$tackle->id],
        'max_hp'                        => 218,
        'current_hp'                    => 218,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    Battle::factory()->for($user)->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 3,
            player_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'species_id' => $eevee->id, 'current_hp' => 0]),
                new Combatant(...[
                    ...$combatant,
                    'pokemon_id' => 'pokemon-1',
                    'is_shiny'   => true,
                    'gender'     => PokemonGender::FEMALE,
                    'boost'      => 12,
                    'move_ids'   => [$growl->id, $tackle->id],
                    'current_hp' => 150,
                    'ailment'    => Ailment::BURN,
                ]),
            ], active_index: 1),
            opponent_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'species_id' => $pidgey->id, 'level' => 34, 'current_hp' => 135]),
            ], active_index: 0),
            is_awaiting_send_out: false,
            log: ['A wild Pidgey appeared!', 'Go! Shiny Pikachu!'],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Battle/pages/Battle/index')
            ->where('battle', [
                'type' => BattleType::WILD->value,
                'turn' => 3,
                'log'  => ['A wild Pidgey appeared!', 'Go! Shiny Pikachu!'],
            ])
            ->where('player', [
                'name'      => 'Shiny Pikachu',
                'gender'    => PokemonGender::FEMALE->value,
                'level'     => 50,
                'boost'     => 12,
                'isShiny'   => true,
                'ailment'   => Ailment::BURN->value,
                'sprite'    => $pikachu->getDisplayBackSprite(PokemonGender::FEMALE, shiny: true),
                'currentHp' => 150,
                'maxHp'     => 218,
                'team'      => [
                    ['name' => 'Eevee', 'isFainted' => true],
                    ['name' => 'Shiny Pikachu', 'isFainted' => false],
                ],
            ])
            ->where('opponent', [
                'name'         => 'Pidgey',
                'gender'       => PokemonGender::MALE->value,
                'level'        => 34,
                'boost'        => 0,
                'isShiny'      => false,
                'ailment'      => null,
                'sprite'       => $pidgey->getDisplayFrontSprite(PokemonGender::MALE, shiny: false),
                'hpPercentage' => 62,
                'team'         => [
                    ['name' => 'Pidgey', 'isFainted' => false],
                ],
            ])
            ->has('moves', 2)
            ->where('moves.0', [
                'id'            => $growl->id,
                'name'          => 'Growl',
                'type'          => PokemonType::NORMAL->value,
                'power'         => null,
                'isZMove'       => false,
                'isDynamaxMove' => false,
            ])
            ->where('moves.1.id', $tackle->id)
        );
});

it('shows the opponent hp only as a percentage, rounded up', function (int $currentHp, int $expectedPercentage) {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create();
    $tackle  = Move::factory()->plainDamage()->create();

    $combatant = [
        'pokemon_id'                    => null,
        'species_id'                    => $pikachu->id,
        'level'                         => 50,
        'boost'                         => 0,
        'is_shiny'                      => false,
        'gender'                        => PokemonGender::MALE,
        'types'                         => [PokemonType::NORMAL],
        'move_ids'                      => [$tackle->id],
        'max_hp'                        => 218,
        'current_hp'                    => 218,
        'ailment'                       => null,
        'ailment_turns_left'            => 0,
        'confusion_turns_left'          => 0,
        'has_entered_field'             => true,
        'used_once_per_battle_move_ids' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'current_hp' => $currentHp])], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('opponent.hpPercentage', $expectedPercentage)
            ->missing('opponent.currentHp')
            ->missing('opponent.maxHp')
        );
})->with([
    'full'     => [218, 100],
    'part'     => [135, 62],
    'a sliver' => [1, 1],
    'fainted'  => [0, 0],
]);

it('redirects to the dashboard without a battle in progress', function (BattleStatus $status) {
    $user = User::factory()->create();

    Battle::factory()->for($user)->create(['status' => $status]);
    Battle::factory()->create(['status' => BattleStatus::ONGOING]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertRedirectToRoute('dashboard.web.home');
})->with([
    'won'       => [BattleStatus::WON],
    'lost'      => [BattleStatus::LOST],
    'forfeited' => [BattleStatus::FORFEITED],
]);
