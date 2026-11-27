<?php

declare(strict_types=1);

use App\Missions\Actions\EnsureDailyMissionsAction;
use Shared\Enums\MissionDifficulty;
use Shared\Enums\MissionGoal;
use Shared\Enums\MissionKind;
use Shared\Models\Achievement;
use Shared\Models\Pokemon;
use Shared\Models\User;
use Shared\Models\UserMission;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\travel;

it('creates three dailies for today once', function () {
    $user = User::factory()->create();

    $firstDailies  = new EnsureDailyMissionsAction()->handle($user);
    $secondDailies = new EnsureDailyMissionsAction()->handle($user);

    assertDatabaseCount(UserMission::class, 3);

    expect($firstDailies->pluck('difficulty')->all())->toBe([MissionDifficulty::EASY, MissionDifficulty::MEDIUM, MissionDifficulty::HARD])
        ->and($firstDailies->every(fn (UserMission $mission) => $mission->kind === MissionKind::DAILY && $mission->date->isToday()))->toBeTrue()
        ->and($secondDailies->modelKeys())->toBe($firstDailies->modelKeys());
});

it('creates three new dailies on a new day', function () {
    $user = User::factory()->create();

    $yesterdaysDailies = new EnsureDailyMissionsAction()->handle($user);

    travel(1)->days();

    $todaysDailies = new EnsureDailyMissionsAction()->handle($user);

    assertDatabaseCount(UserMission::class, 6);

    expect($todaysDailies)->toHaveCount(3)
        ->and($todaysDailies->modelKeys())->not->toContain(...$yesterdaysDailies->modelKeys());
});

it('only returns the dailies of the given player', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();

    new EnsureDailyMissionsAction()->handle($otherUser);

    $dailies = new EnsureDailyMissionsAction()->handle($user);

    expect($dailies->pluck('user_id')->unique()->all())->toBe([$user->id]);
});

it('creates the achievement missions once', function () {
    $user        = User::factory()->create();
    $achievement = Achievement::factory()->create(['name' => 'Catch 10 Pokémon', 'target' => 10]);

    new EnsureDailyMissionsAction()->handle($user);
    new EnsureDailyMissionsAction()->handle($user);

    assertDatabaseCount(UserMission::class, 4);

    $mission = UserMission::query()->where('kind', MissionKind::ACHIEVEMENT)->sole();

    expect($mission->achievement_id)->toBe($achievement->id)
        ->and($mission->description)->toBe('Catch 10 Pokémon')
        ->and($mission->target)->toBe(10)
        ->and($mission->progress)->toBe(0)
        ->and($mission->reward->equals($achievement->reward))->toBeTrue()
        ->and($mission->isCompleted())->toBeFalse();
});

it('adds achievements that were added after the first call', function () {
    $user = User::factory()->create();
    Achievement::factory()->create();

    new EnsureDailyMissionsAction()->handle($user);

    Achievement::factory()->create();

    new EnsureDailyMissionsAction()->handle($user);

    assertDatabaseCount(UserMission::class, 5);

    expect(UserMission::query()->where('kind', MissionKind::ACHIEVEMENT)->count())->toBe(2);
});

it('starts a reach goal at the best Pokémon value', function (MissionGoal $goal, string $column, int $target, int $expectedProgress, bool $isCompleted) {
    $user = User::factory()->create();
    Pokemon::factory()->for($user)->create([$column => 40]);
    Pokemon::factory()->for($user)->create([$column => 60]);
    Pokemon::factory()->create([$column => 100]);
    Achievement::factory()->create(['goal' => $goal, 'target' => $target]);

    new EnsureDailyMissionsAction()->handle($user);

    $mission = UserMission::query()->where('kind', MissionKind::ACHIEVEMENT)->sole();

    expect($mission->progress)->toBe($expectedProgress)
        ->and($mission->isCompleted())->toBe($isCompleted);
})->with([
    'level not reached yet' => [MissionGoal::REACH_LEVEL, 'level', 100, 60, false],
    'level already reached' => [MissionGoal::REACH_LEVEL, 'level', 50, 50, true],
    'boost not reached yet' => [MissionGoal::REACH_BOOST, 'boost', 100, 60, false],
    'boost already reached' => [MissionGoal::REACH_BOOST, 'boost', 50, 50, true],
]);
