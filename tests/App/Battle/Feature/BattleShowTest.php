<?php

declare(strict_types=1);

use App\Battle\Enums\BattleScene;
use Inertia\Testing\AssertableInertia as Assert;
use Shared\Enums\Ailment;
use Shared\Enums\Area;
use Shared\Enums\BattleLogKind;
use Shared\Enums\BattleStatus;
use Shared\Enums\BattleType;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonType;
use Shared\Models\Battle;
use Shared\Models\InventoryItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
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
        'pokemonId'                => null,
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 3,
            playerSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'speciesId' => $eevee->id, 'currentHp' => 0]),
                new Combatant(...[
                    ...$combatant,
                    'pokemonId' => 'pokemon-1',
                    'isShiny'   => true,
                    'gender'    => PokemonGender::FEMALE,
                    'boost'     => 12,
                    'moveIds'   => [$growl->id, $tackle->id],
                    'currentHp' => 150,
                    'ailment'   => Ailment::BURN,
                ]),
            ], activeIndex: 1),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'speciesId' => $pidgey->id, 'level' => 34, 'currentHp' => 135]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [
                new BattleLogEntry(0, BattleLogKind::INFO, 'A wild Pidgey appeared!'),
                new BattleLogEntry(0, BattleLogKind::EVENT, 'Go! Shiny Pikachu!'),
                new BattleLogEntry(1, BattleLogKind::MOVE, 'Shiny Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(1, BattleLogKind::DAMAGE, 'It dealt 46 HP damage.'),
                new BattleLogEntry(2, BattleLogKind::MOVE, 'The wild Pidgey used Growl!', 'Growl'),
            ],
            area: Area::FOREST,
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
                'area'   => Area::FOREST->value,
                'turn'   => 3,
                'log'    => [
                    ['turn' => 2, 'isEnd' => false, 'entries' => [
                        ['kind' => BattleLogKind::MOVE->value, 'text' => 'The wild Pidgey used Growl!', 'moveName' => 'Growl'],
                    ]],
                    ['turn' => 1, 'isEnd' => false, 'entries' => [
                        ['kind' => BattleLogKind::MOVE->value, 'text' => 'Shiny Pikachu used Tackle!', 'moveName' => 'Tackle'],
                        ['kind' => BattleLogKind::DAMAGE->value, 'text' => 'It dealt 46 HP damage.', 'moveName' => null],
                    ]],
                    ['turn' => 0, 'isEnd' => false, 'entries' => [
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
                    [
                        'name'        => 'Eevee',
                        'portrait'    => $eevee->portraits->regular,
                        'gender'      => PokemonGender::MALE->value,
                        'level'       => 50,
                        'boost'       => 0,
                        'isShiny'     => false,
                        'statBonuses' => ['hp' => 0, 'attack' => 0, 'damageTaken' => 0],
                        'types'       => [PokemonType::NORMAL->value],
                        'moves'       => [['name' => 'Tackle', 'type' => PokemonType::NORMAL->value, 'power' => 40]],
                        'currentHp'   => 0,
                        'maxHp'       => 218,
                        'ailment'     => null,
                        'isFainted'   => true,
                        'isActive'    => false,
                    ],
                    [
                        'name'        => 'Shiny Pikachu',
                        'portrait'    => $pikachu->portraits->shiny,
                        'gender'      => PokemonGender::FEMALE->value,
                        'level'       => 50,
                        'boost'       => 12,
                        'isShiny'     => true,
                        'statBonuses' => ['hp' => 40, 'attack' => 40, 'damageTaken' => -33],
                        'types'       => [PokemonType::NORMAL->value],
                        'moves'       => [
                            ['name' => 'Growl', 'type' => PokemonType::NORMAL->value, 'power' => null],
                            ['name' => 'Tackle', 'type' => PokemonType::NORMAL->value, 'power' => 40],
                        ],
                        'currentHp' => 150,
                        'maxHp'     => 218,
                        'ailment'   => Ailment::BURN->value,
                        'isFainted' => false,
                        'isActive'  => true,
                    ],
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
                    [
                        'name'        => 'Pidgey',
                        'portrait'    => $pidgey->portraits->regular,
                        'gender'      => PokemonGender::MALE->value,
                        'level'       => 34,
                        'boost'       => 0,
                        'isShiny'     => false,
                        'statBonuses' => ['hp' => 0, 'attack' => 0, 'damageTaken' => 0],
                        'types'       => [PokemonType::NORMAL->value],
                        'moves'       => [['name' => 'Tackle', 'type' => PokemonType::NORMAL->value, 'power' => 40]],
                        'currentHp'   => 135,
                        'maxHp'       => 218,
                        'isFainted'   => false,
                        'isActive'    => true,
                        'isRevealed'  => true,
                    ],
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
        'pokemonId'                => null,
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'currentHp' => $currentHp])], activeIndex: 0),
            isAwaitingSendOut: false,
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
        'pokemonId'                => null,
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'type'   => $type,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            isAwaitingSendOut: false,
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
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 4,
            playerSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'currentHp' => 0]),
                new Combatant(...[...$combatant, 'pokemonId' => 'pokemon-2', 'speciesId' => $eevee->id, 'isShiny' => true, 'level' => 40, 'currentHp' => 90, 'maxHp' => 180, 'hasEnteredField' => false]),
            ], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => null])], activeIndex: 0),
            isAwaitingSendOut: true,
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
    $zoroark = Species::factory()->create(['name' => 'Zoroark']);
    $tackle  = Move::factory()->plainDamage()->create();

    $combatant = [
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 25,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => $status,
        'state'  => new BattleState(
            turn: 6,
            playerSide: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemonId' => 'benched-pokemon', 'speciesId' => $eevee->id, 'gender' => PokemonGender::FEMALE, 'hasEnteredField' => false]),
                new Combatant(...[...$combatant, 'pokemonId' => 'max-level-pokemon', 'speciesId' => $zoroark->id, 'level' => 100]),
            ], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => null])], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [
                new BattleLogEntry(5, BattleLogKind::MOVE, 'Pikachu used Tackle!', 'Tackle'),
                new BattleLogEntry(6, BattleLogKind::EVENT, 'Pikachu gained 1,875 XP!'),
            ],
            experienceGains: [
                new ExperienceGain(combatantIndex: 0, earnedExperience: 1_875, levelBefore: 25, levelAfter: 26, isFromExperienceShare: false, isAtMaxLevel: false),
                new ExperienceGain(combatantIndex: 1, earnedExperience: 937, levelBefore: 25, levelAfter: 25, isFromExperienceShare: true, isAtMaxLevel: false),
                new ExperienceGain(combatantIndex: 2, earnedExperience: 0, levelBefore: 100, levelAfter: 100, isFromExperienceShare: false, isAtMaxLevel: true),
            ],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('battle.status', $status->value)
            ->where('result.experienceGains', [
                ['name' => 'Pikachu', 'portrait' => $pikachu->portraits->regular, 'gender' => PokemonGender::MALE->value, 'earnedExperience' => 1_875, 'levelBefore' => 25, 'levelAfter' => 26, 'isFromExperienceShare' => false, 'isAtMaxLevel' => false],
                ['name' => 'Eevee', 'portrait' => $eevee->portraits->regular, 'gender' => PokemonGender::FEMALE->value, 'earnedExperience' => 937, 'levelBefore' => 25, 'levelAfter' => 25, 'isFromExperienceShare' => true, 'isAtMaxLevel' => false],
                ['name' => 'Zoroark', 'portrait' => $zoroark->portraits->regular, 'gender' => PokemonGender::MALE->value, 'earnedExperience' => 0, 'levelBefore' => 100, 'levelAfter' => 100, 'isFromExperienceShare' => false, 'isAtMaxLevel' => true],
            ])
            ->where('battle.isAwaitingSendOut', false)
            ->where('battle.log.0.isEnd', true)
            ->where('battle.log.1.isEnd', false)
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
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 25,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::FORFEITED,
        'state'  => new BattleState(
            turn: 2,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => null])], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
            experienceGains: [new ExperienceGain(combatantIndex: 0, earnedExperience: 0, levelBefore: 25, levelAfter: 25, isFromExperienceShare: false, isAtMaxLevel: false)],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('result.experienceGains', [])
            ->where('result.caughtPokemon', null)
        );
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

it('shows the whole opponent team in a trainer battle, with the boost of each pokemon', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create();
    $geodude = Species::factory()->create(['name' => 'Geodude']);
    $onix    = Species::factory()->create(['name' => 'Onix']);
    $tackle  = Move::factory()->plainDamage()->create();

    $combatant = [
        'pokemonId'                => null,
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 5,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => false,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'type'   => BattleType::TRAINER,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            playerSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'hasEnteredField' => true])], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'speciesId' => $geodude->id, 'hasEnteredField' => true, 'currentHp' => 0]),
                new Combatant(...[...$combatant, 'hasEnteredField' => true]),
                new Combatant(...[...$combatant, 'speciesId' => $onix->id]),
            ], activeIndex: 1),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('opponent.team', 3)
            ->where('opponent.team.0.name', 'Geodude')
            ->where('opponent.team.0.isFainted', true)
            ->where('opponent.team.0.boost', 5)
            ->where('opponent.team.1.isActive', true)
            ->where('opponent.team.1.boost', 5)
            ->where('opponent.team.2.name', 'Onix')
            ->where('opponent.team.2.boost', 5)
            ->where('opponent.team.2.isRevealed', true)
        );
});

it('hides the opponent pokemon that have not been sent out yet in a gym battle, with no types or moves', function (BattleType $type) {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create();
    $onix    = Species::factory()->create(['name' => 'Onix']);
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);
    $ember   = Move::factory()->plainDamage()->create(['name' => 'Ember', 'type' => PokemonType::FIRE, 'power' => 40]);

    $combatant = [
        'pokemonId'                => null,
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'type'   => $type,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [
                new Combatant(...[...$combatant, 'isShiny' => true, 'boost' => 7, 'types' => [PokemonType::FIRE], 'moveIds' => [$ember->id]]),
                new Combatant(...[...$combatant, 'speciesId' => $onix->id, 'hasEnteredField' => false]),
            ], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('opponent.team.0.isRevealed', true)
            ->where('opponent.team.0.boost', 7)
            ->where('opponent.team.0.isShiny', true)
            ->where('opponent.team.0.statBonuses', ['hp' => 33.8, 'attack' => 33.8, 'damageTaken' => -29.9])
            ->where('opponent.team.0.types', [PokemonType::FIRE->value])
            ->where('opponent.team.0.moves', [['name' => 'Ember', 'type' => PokemonType::FIRE->value, 'power' => 40]])
            ->where('opponent.team.1', [
                'name'        => null,
                'portrait'    => null,
                'gender'      => null,
                'level'       => null,
                'boost'       => null,
                'isShiny'     => null,
                'statBonuses' => null,
                'types'       => null,
                'moves'       => null,
                'currentHp'   => null,
                'maxHp'       => null,
                'isFainted'   => false,
                'isActive'    => false,
                'isRevealed'  => false,
            ])
        );
})->with([
    'gym'        => [BattleType::GYM],
    'elite four' => [BattleType::ELITE_FOUR],
]);

it('leaves a move that no longer exists out of a pokemon details', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create();
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);
    $removed = Move::factory()->plainDamage()->create();

    $combatant = [
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            playerSide: new BattleSide(combatants: [
                new Combatant(...$combatant),
                new Combatant(...[...$combatant, 'pokemonId' => 'pokemon-2', 'moveIds' => [$removed->id, $tackle->id]]),
            ], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => null])], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    $removed->delete();

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('player.team.1.moves', [['name' => 'Tackle', 'type' => PokemonType::NORMAL->value, 'power' => 40]])
        );
});

it('leaves a move that no longer exists out of the active pokemon moves', function () {
    $user    = User::factory()->create();
    $pikachu = Species::factory()->create();
    $tackle  = Move::factory()->plainDamage()->create(['name' => 'Tackle', 'type' => PokemonType::NORMAL, 'power' => 40]);
    $removed = Move::factory()->plainDamage()->create();

    $combatant = [
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 50,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$removed->id, $tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 1,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => null])], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    $removed->delete();

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('moves', 1)
            ->where('moves.0.id', $tackle->id)
            ->where('player.team.0.moves', [['name' => 'Tackle', 'type' => PokemonType::NORMAL->value, 'power' => 40]])
        );
});

it('lists the owned balls with their multiplier right now, boosted balls first', function () {
    $user      = User::factory()->create();
    $pikachu   = Species::factory()->create();
    $pidgey    = Species::factory()->create(['types' => [PokemonType::NORMAL, PokemonType::FLYING]]);
    $tackle    = Move::factory()->plainDamage()->create();
    $pokeBall  = Pokeball::factory()->create(['slug' => 'poke-ball', 'name' => 'Poké Ball', 'catch_rate' => 1]);
    $greatBall = Pokeball::factory()->create(['slug' => 'great-ball', 'name' => 'Great Ball', 'catch_rate' => 1.5]);
    $fastBall  = Pokeball::factory()->create(['slug' => 'fast-ball', 'name' => 'Fast Ball', 'catch_rate' => 1]);
    $netBall   = Pokeball::factory()->create(['slug' => 'net-ball', 'name' => 'Net Ball', 'catch_rate' => 1]);
    $ultraBall = Pokeball::factory()->create(['slug' => 'ultra-ball', 'name' => 'Ultra Ball', 'catch_rate' => 2]);

    InventoryItem::factory()->for($user)->pokeball($pokeBall)->create(['quantity' => 18]);
    InventoryItem::factory()->for($user)->pokeball($greatBall)->create(['quantity' => 5]);
    InventoryItem::factory()->for($user)->pokeball($fastBall)->create(['quantity' => 4]);
    InventoryItem::factory()->for($user)->pokeball($netBall)->create(['quantity' => 2]);
    InventoryItem::factory()->for($user)->pokeball($ultraBall)->create(['quantity' => 0]);
    InventoryItem::factory()->pokeball($ultraBall)->create(['quantity' => 9]);

    $combatant = [
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pikachu->id,
        'level'                    => 25,
        'boost'                    => 0,
        'isShiny'                  => false,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'type'   => BattleType::WILD,
        'status' => BattleStatus::ONGOING,
        'state'  => new BattleState(
            turn: 2,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => null, 'speciesId' => $pidgey->id])], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertInertia(fn (Assert $page) => $page->where('pokeballs', [
            ['slug' => 'fast-ball', 'name' => 'Fast Ball', 'sprite' => $fastBall->sprite, 'quantity' => 4, 'multiplier' => 3.5, 'isBoosted' => true],
            ['slug' => 'great-ball', 'name' => 'Great Ball', 'sprite' => $greatBall->sprite, 'quantity' => 5, 'multiplier' => 1.5, 'isBoosted' => false],
            ['slug' => 'net-ball', 'name' => 'Net Ball', 'sprite' => $netBall->sprite, 'quantity' => 2, 'multiplier' => 1, 'isBoosted' => false],
            ['slug' => 'poke-ball', 'name' => 'Poké Ball', 'sprite' => $pokeBall->sprite, 'quantity' => 18, 'multiplier' => 1, 'isBoosted' => false],
        ]));
});

it('lists no balls outside an ongoing wild battle', function (BattleType $type, BattleStatus $status) {
    $user     = User::factory()->create();
    $pikachu  = Species::factory()->create();
    $tackle   = Move::factory()->plainDamage()->create();
    $pokeBall = Pokeball::factory()->create();

    InventoryItem::factory()->for($user)->pokeball($pokeBall)->create(['quantity' => 5]);

    $combatant = new Combatant(
        pokemonId: null,
        speciesId: $pikachu->id,
        level: 25,
        boost: 0,
        isShiny: false,
        gender: PokemonGender::MALE,
        types: [PokemonType::NORMAL],
        moveIds: [$tackle->id],
        maxHp: 218,
        currentHp: 218,
        ailment: null,
        ailmentTurnsLeft: 0,
        confusionTurnsLeft: 0,
        hasEnteredField: true,
        usedOncePerBattleMoveIds: [],
    );

    Battle::factory()->for($user)->create([
        'type'   => $type,
        'status' => $status,
        'state'  => new BattleState(
            turn: 2,
            playerSide: new BattleSide(combatants: [$combatant], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [$combatant], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertInertia(fn (Assert $page) => $page->where('pokeballs', []));
})->with([
    'trainer battle'    => [BattleType::TRAINER, BattleStatus::ONGOING],
    'ended wild battle' => [BattleType::WILD, BattleStatus::FORFEITED],
]);

it('shows the caught pokemon and the ball it was caught with on the end screen', function (bool $isInParty) {
    $user      = User::factory()->create();
    $pidgey    = Species::factory()->create(['name' => 'Pidgey']);
    $ultraBall = Pokeball::factory()->create(['name' => 'Ultra Ball']);
    $tackle    = Move::factory()->plainDamage()->create();
    $caught    = Pokemon::factory()->for($user)->shiny()->create(['species_id' => $pidgey->id, 'pokeball_id' => $ultraBall->id, 'in_party' => $isInParty]);

    $combatant = [
        'pokemonId'                => 'pokemon-1',
        'speciesId'                => $pidgey->id,
        'level'                    => 25,
        'boost'                    => 0,
        'isShiny'                  => true,
        'gender'                   => PokemonGender::MALE,
        'types'                    => [PokemonType::NORMAL],
        'moveIds'                  => [$tackle->id],
        'maxHp'                    => 218,
        'currentHp'                => 218,
        'ailment'                  => null,
        'ailmentTurnsLeft'         => 0,
        'confusionTurnsLeft'       => 0,
        'hasEnteredField'          => true,
        'usedOncePerBattleMoveIds' => [],
    ];

    Battle::factory()->for($user)->create([
        'status' => BattleStatus::CAUGHT,
        'state'  => new BattleState(
            turn: 2,
            playerSide: new BattleSide(combatants: [new Combatant(...$combatant)], activeIndex: 0),
            opponentSide: new BattleSide(combatants: [new Combatant(...[...$combatant, 'pokemonId' => null])], activeIndex: 0),
            isAwaitingSendOut: false,
            log: [],
            caughtPokemonId: $caught->id,
        ),
    ]);

    actingAs($user)
        ->get(route('battle.web.show'))
        ->assertInertia(fn (Assert $page) => $page->where('result.caughtPokemon', [
            'name'         => 'Shiny Pidgey',
            'pokeballName' => 'Ultra Ball',
            'isInParty'    => $isInParty,
        ]));
})->with([
    'in the party' => [true],
    'in the box'   => [false],
]);
