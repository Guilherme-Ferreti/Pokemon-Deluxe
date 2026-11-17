<?php

declare(strict_types=1);

use App\Battle\Enums\BattleScene;
use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\Ailment;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\Move;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;
use Shared\ValueObjects\BattleLogEntry;
use Shared\ValueObjects\BattleSide;
use Shared\ValueObjects\BattleState;
use Shared\ValueObjects\Combatant;
use Shared\ValueObjects\ExperienceGain;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\travel;

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
            log: [
                new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
                new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Shiny Pikachu!'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Shiny Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'The wild Pidgey lost 46 HP.'),
                new BattleLogEntry(2, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Battle/pages/Battle/index')
            ->where('battle', [
                'type'   => BattleType::WILD->value,
                'scene'  => BattleScene::GRASS_FIELD->value,
                'status' => BattleStatus::ONGOING->value,
                'turn'   => 3,
                'log'    => [
                    ['turn' => 2, 'entries' => [
                        ['kind' => BattleLogKind::MOVE->value, 'text' => 'The wild Pidgey used Growl!', 'moveName' => 'Growl'],
                    ]],
                    ['turn' => 1, 'entries' => [
                        ['kind' => BattleLogKind::MOVE->value, 'text' => 'Shiny Pikachu used Tackle!', 'moveName' => 'Tackle'],
                        ['kind' => BattleLogKind::DAMAGE->value, 'text' => 'The wild Pidgey lost 46 HP.', 'moveName' => null],
                    ]],
                    ['turn' => 0, 'entries' => [
                        ['kind' => BattleLogKind::INFO->value, 'text' => 'A wild Pidgey appeared!', 'moveName' => null],
                        ['kind' => BattleLogKind::EVENT->value, 'text' => 'Go! Shiny Pikachu!', 'moveName' => null],
                    ]],
                ],
                'isAwaitingSendOut' => false,
            ])
            ->where('result', null)
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
                    ['name' => 'Eevee', 'portrait' => $eevee->portraits->regular, 'gender' => PokemonGender::MALE->value, 'level' => 50, 'boost' => 0, 'currentHp' => 0, 'maxHp' => 218, 'ailment' => null, 'isFainted' => true, 'isActive' => false],
                    ['name' => 'Shiny Pikachu', 'portrait' => $pikachu->portraits->shiny, 'gender' => PokemonGender::FEMALE->value, 'level' => 50, 'boost' => 12, 'currentHp' => 150, 'maxHp' => 218, 'ailment' => Ailment::BURN->value, 'isFainted' => false, 'isActive' => true],
                ],
            ])
            ->where('opponent', [
                'name'      => 'Pidgey',
                'gender'    => PokemonGender::MALE->value,
                'level'     => 34,
                'boost'     => 0,
                'isShiny'   => false,
                'ailment'   => null,
                'sprite'    => $pidgey->getDisplayFrontSprite(PokemonGender::MALE, shiny: false),
                'currentHp' => 135,
                'maxHp'     => 218,
                'team'      => [
                    ['name' => 'Pidgey', 'isFainted' => false],
                ],
            ])
            ->has('moves', 2)
            ->where('moves.0', [
                'id'            => $growl->id,
                'name'          => 'Growl',
                'type'          => PokemonType::NORMAL->value,
                'isZMove'       => false,
                'isDynamaxMove' => false,
            ])
            ->where('moves.1.id', $tackle->id)
        );
});

it('shows the opponent hp as numbers', function (int $currentHp) {
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
            ->where('opponent.currentHp', $currentHp)
            ->where('opponent.maxHp', 218)
            ->missing('opponent.hpPercentage')
        );
})->with([
    'full'    => [218],
    'part'    => [135],
    'fainted' => [0],
]);

it('picks the scene from the battle type', function (BattleType $type, BattleScene $expectedScene) {
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
        'type'   => $type,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('battle.scene', $expectedScene->value)
        );
})->with([
    'wild'         => [BattleType::WILD, BattleScene::GRASS_FIELD],
    'trainer'      => [BattleType::TRAINER, BattleScene::TRAINER_ARENA],
    'gym'          => [BattleType::GYM, BattleScene::GYM],
    'elite four'   => [BattleType::ELITE_FOUR, BattleScene::INDIGO_PLATEAU],
    'battle tower' => [BattleType::BATTLE_TOWER, BattleScene::BATTLE_TOWER],
]);

it('asks for a send-out after a faint', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);
    $tackle  = Move::factory()->plainDamage()->create();

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
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
            turn: 4,
            player_side: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'current_hp' => 0]),
                new Combatant(...[...$combatant, 'pokemon_id' => 'pokemon-2', 'species_id' => $eevee->id, 'is_shiny' => true, 'level' => 40, 'current_hp' => 90, 'max_hp' => 180, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: true,
            log: [],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('battle.isAwaitingSendOut', true)
        );
});

it('shows the end screen with the XP each pokemon earned', function (BattleStatus $status) {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create(['name' => 'Pikachu']);
    $eevee   = Species::factory()->create(['name' => 'Eevee']);
    $tackle  = Move::factory()->plainDamage()->create();
    $pokemon = Pokemon::factory()->for($user)->create(['level' => 26, 'experience' => 26_875]);

    $combatant = [
        'pokemon_id'                    => $pokemon->id,
        'species_id'                    => $pikachu->id,
        'level'                         => 25,
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
        'status' => $status,
        'state'  => new BattleState(
            turn: 6,
            player_side: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemon_id' => 'released-pokemon', 'species_id' => $eevee->id]),
                new Combatant(...[...$combatant, 'pokemon_id' => 'benched-pokemon', 'species_id' => $eevee->id, 'has_entered_field' => false]),
            ], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
            experience_gains: [
                new ExperienceGain(combatant_index: 0, earned_experience: 1_875, level_before: 25, level_after: 26),
                new ExperienceGain(combatant_index: 1, earned_experience: 937, level_before: 25, level_after: 25),
            ],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('battle.status', $status->value)
            ->where('result.experienceGains', [
                ['name' => 'Pikachu', 'portrait' => $pikachu->portraits->regular, 'earnedExperience' => 1_875, 'levelBefore' => 25, 'levelAfter' => 26, 'experienceProgress' => 87],
                ['name' => 'Eevee', 'portrait' => $eevee->portraits->regular, 'earnedExperience' => 937, 'levelBefore' => 25, 'levelAfter' => 25, 'experienceProgress' => null],
            ])
            ->where('battle.isAwaitingSendOut', false)
        );
})->with([
    'won'       => [BattleStatus::WON],
    'lost'      => [BattleStatus::LOST],
    'forfeited' => [BattleStatus::FORFEITED],
]);

it('shows no XP rows when nothing was earned', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create();
    $tackle  = Move::factory()->plainDamage()->create();

    $combatant = [
        'pokemon_id'                    => 'pokemon-1',
        'species_id'                    => $pikachu->id,
        'level'                         => 25,
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
        'status' => BattleStatus::FORFEITED,
        'state'  => new BattleState(
            turn: 2,
            player_side: new BattleSide(combatants: [new Combatant(...$combatant)], active_index: 0),
            opponent_side: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemon_id' => null])], active_index: 0),
            is_awaiting_send_out: false,
            log: [],
            experience_gains: [new ExperienceGain(combatant_index: 0, earned_experience: 0, level_before: 25, level_after: 25)],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertInertia(fn (Assert $page) => $page->where('result.experienceGains', []));
});

it('redirects to the dashboard once the end screen has expired', function () {
    $user = User::factory()->create();

    Battle::factory()->for($user)->create(['status' => BattleStatus::WON]);

    travel(11)->minutes();

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertRedirectToRoute('dashboard.web.home');
});

it('redirects to the dashboard without a battle in progress or recently ended', function () {
    $user = User::factory()->create();

    Battle::factory()->create(['status' => BattleStatus::ONGOING]);
    Battle::factory()->create(['status' => BattleStatus::WON]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertRedirectToRoute('dashboard.web.home');
});
