<?php

declare(strict_types=1);

use App\Missions\Mechanics\DailyMissionRoller;
use App\Missions\ValueObjects\NewDailyMission;
use Database\Seeders\PokeballSeeder;
use Database\Seeders\SpecialItemSeeder;
use Shared\Enums\Area;
use Shared\Enums\InventoryItemType;
use Shared\Enums\MissionDifficulty;
use Shared\Enums\MissionGoal;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\Models\EvolutionItem;
use Shared\Models\Move;
use Shared\Models\Pokeball;
use Shared\Models\SpecialItem;
use Shared\ValueObjects\MissionReward;
use Shared\ValueObjects\MissionRewardItem;

use function Pest\Laravel\seed;

it('rolls one mission per difficulty from the shipped templates', function () {
    foreach (range(1, 20) as $attempt) {
        $missions = new DailyMissionRoller()->roll();

        expect(array_map(fn (NewDailyMission $mission) => $mission->difficulty, $missions))
            ->toBe([MissionDifficulty::EASY, MissionDifficulty::MEDIUM, MissionDifficulty::HARD]);
    }
});

it('fills in the rolled target and parameters', function () {
    $easyTemplate = [
        'difficulty'  => 'easy',
        'goal'        => 'catch-pokemon-of-type',
        'description' => 'Catch {target} {type} Pokémon',
        'target'      => [3, 5],
        'parameters'  => ['type' => 'any'],
        'reward'      => ['money' => 1500, 'items' => [['item_type' => 'pokeball', 'slug' => 'great-ball', 'quantity' => 3]]],
        'enabled'     => true,
    ];
    $mediumTemplate = [
        'difficulty'  => 'medium',
        'goal'        => 'catch-pokemon-in-area',
        'description' => 'Catch {target} Pokémon in the {area}',
        'target'      => [3, 5],
        'parameters'  => ['area' => 'any'],
        'reward'      => ['money' => 3000, 'items' => [['random' => 'normal-tm', 'quantity' => 1]]],
        'enabled'     => true,
    ];
    $hardTemplate = [
        'difficulty'  => 'hard',
        'goal'        => 'catch-pokemon-at-level',
        'description' => 'Catch a Pokémon at level {level} or higher',
        'target'      => [1, 1],
        'parameters'  => ['level' => [50, 60]],
        'reward'      => ['money' => 0, 'items' => []],
        'enabled'     => true,
    ];

    config(['missions.daily_templates' => [$easyTemplate, $mediumTemplate, $hardTemplate]]);

    RandomHelper::fake([$easyTemplate, 4, PokemonType::WATER, $mediumTemplate, 3, Area::COAST, $hardTemplate, 1, 55]);

    $missions = new DailyMissionRoller()->roll();

    expect($missions[0]->equals(new NewDailyMission(
        difficulty: MissionDifficulty::EASY,
        goal: MissionGoal::CATCH_POKEMON_OF_TYPE,
        goalParameters: ['type' => 'water'],
        target: 4,
        description: 'Catch 4 Water Pokémon',
        reward: new MissionReward(money: 1_500, items: [new MissionRewardItem(quantity: 3, itemType: InventoryItemType::POKEBALL, slug: 'great-ball')]),
    )))->toBeTrue()
        ->and($missions[1]->goalParameters)->toBe(['area' => 'coast'])
        ->and($missions[1]->description)->toBe('Catch 3 Pokémon in the Coast')
        ->and($missions[2]->goalParameters)->toBe(['level' => 55])
        ->and($missions[2]->description)->toBe('Catch a Pokémon at level 55 or higher');
});

it('uses the singular description for a target of one', function (int $target, string $description) {
    config(['missions.daily_templates' => [
        ['difficulty' => 'easy', 'goal' => 'hatch-eggs', 'description' => 'Hatch {target} egg|Hatch {target} eggs', 'target' => [1, 2], 'parameters' => [], 'reward' => ['money' => 2000, 'items' => []], 'enabled' => true],
        ['difficulty' => 'medium', 'goal' => 'evolve-pokemon', 'description' => 'Evolve {target} Pokémon', 'target' => [1, 2], 'parameters' => [], 'reward' => ['money' => 3000, 'items' => []], 'enabled' => true],
        ['difficulty' => 'hard', 'goal' => 'catch-shiny', 'description' => 'Catch {target} shiny Pokémon', 'target' => [1, 1], 'parameters' => [], 'reward' => ['money' => 0, 'items' => []], 'enabled' => true],
    ]]);

    RandomHelper::fake([config('missions.daily_templates.0'), $target, config('missions.daily_templates.1'), 1, config('missions.daily_templates.2'), 1]);

    expect(new DailyMissionRoller()->roll()[0]->description)->toBe($description);
})->with([
    'one egg'  => [1, 'Hatch 1 egg'],
    'two eggs' => [2, 'Hatch 2 eggs'],
]);

it('rolls targets and parameters within their ranges', function () {
    config(['missions.daily_templates' => [
        ['difficulty' => 'easy', 'goal' => 'catch-pokemon', 'description' => 'Catch {target} Pokémon', 'target' => [5, 10], 'parameters' => [], 'reward' => ['money' => 1000, 'items' => []], 'enabled' => true],
        ['difficulty' => 'medium', 'goal' => 'catch-pokemon-of-type', 'description' => 'Catch {target} {type} Pokémon', 'target' => [3, 5], 'parameters' => ['type' => 'any'], 'reward' => ['money' => 3000, 'items' => []], 'enabled' => true],
        ['difficulty' => 'hard', 'goal' => 'catch-pokemon-at-level', 'description' => 'Catch a Pokémon at level {level} or higher', 'target' => [1, 1], 'parameters' => ['level' => [50, 60]], 'reward' => ['money' => 0, 'items' => []], 'enabled' => true],
    ]]);

    foreach (range(1, 50) as $attempt) {
        [$easy, $medium, $hard] = new DailyMissionRoller()->roll();

        expect($easy->target)->toBeGreaterThanOrEqual(5)->toBeLessThanOrEqual(10)
            ->and($medium->target)->toBeGreaterThanOrEqual(3)->toBeLessThanOrEqual(5)
            ->and(PokemonType::tryFrom($medium->goalParameters['type']))->not->toBeNull()
            ->and($hard->target)->toBe(1)
            ->and($hard->goalParameters['level'])->toBeGreaterThanOrEqual(50)->toBeLessThanOrEqual(60);
    }
});

it('never picks a disabled template', function () {
    config(['missions.daily_templates' => [
        ['difficulty' => 'easy', 'goal' => 'catch-pokemon', 'description' => 'Catch {target} Pokémon', 'target' => [5, 10], 'parameters' => [], 'reward' => ['money' => 1000, 'items' => []], 'enabled' => true],
        ['difficulty' => 'easy', 'goal' => 'hatch-eggs', 'description' => 'Hatch {target} eggs', 'target' => [1, 2], 'parameters' => [], 'reward' => ['money' => 2000, 'items' => []], 'enabled' => false],
        ['difficulty' => 'medium', 'goal' => 'evolve-pokemon', 'description' => 'Evolve {target} Pokémon', 'target' => [1, 2], 'parameters' => [], 'reward' => ['money' => 3000, 'items' => []], 'enabled' => true],
        ['difficulty' => 'medium', 'goal' => 'win-trainer-battles', 'description' => 'Win {target} battles against trainers', 'target' => [3, 5], 'parameters' => [], 'reward' => ['money' => 3000, 'items' => []], 'enabled' => false],
        ['difficulty' => 'hard', 'goal' => 'catch-shiny', 'description' => 'Catch {target} shiny Pokémon', 'target' => [1, 1], 'parameters' => [], 'reward' => ['money' => 0, 'items' => []], 'enabled' => true],
    ]]);

    foreach (range(1, 50) as $attempt) {
        $goals = array_map(fn (NewDailyMission $mission) => $mission->goal, new DailyMissionRoller()->roll());

        expect($goals)->toBe([MissionGoal::CATCH_POKEMON, MissionGoal::EVOLVE_POKEMON, MissionGoal::CATCH_SHINY]);
    }
});

it('refuses a difficulty without an enabled template', function () {
    config(['missions.daily_templates' => [
        ['difficulty' => 'easy', 'goal' => 'catch-pokemon', 'description' => 'Catch {target} Pokémon', 'target' => [5, 10], 'parameters' => [], 'reward' => ['money' => 1000, 'items' => []], 'enabled' => true],
        ['difficulty' => 'medium', 'goal' => 'evolve-pokemon', 'description' => 'Evolve {target} Pokémon', 'target' => [1, 2], 'parameters' => [], 'reward' => ['money' => 3000, 'items' => []], 'enabled' => true],
        ['difficulty' => 'hard', 'goal' => 'climb-tower-floors', 'description' => 'Climb {target} floors', 'target' => [10, 15], 'parameters' => [], 'reward' => ['money' => 0, 'items' => []], 'enabled' => false],
    ]]);

    expect(fn () => new DailyMissionRoller()->roll())->toThrow(LogicException::class);
});

it('gives only items that exist in the shipped templates', function () {
    seed([PokeballSeeder::class, SpecialItemSeeder::class]);

    $concreteItems = collect(config()->array('missions.daily_templates'))
        ->flatMap(fn (array $template) => MissionReward::fromArray($template['reward'])->items)
        ->reject(fn (MissionRewardItem $item) => $item->isRandom());

    expect($concreteItems)->not->toBeEmpty();

    foreach ($concreteItems as $item) {
        $model = match ($item->itemType) {
            InventoryItemType::POKEBALL       => Pokeball::class,
            InventoryItemType::SPECIAL_ITEM   => SpecialItem::class,
            InventoryItemType::EVOLUTION_ITEM => EvolutionItem::class,
            InventoryItemType::TM             => Move::class,
            null                              => throw new LogicException('A concrete reward item has an item type.'),
        };

        expect($model::query()->where('slug', $item->slug)->exists())->toBeTrue("[{$item->slug}] does not exist.");
    }
});
