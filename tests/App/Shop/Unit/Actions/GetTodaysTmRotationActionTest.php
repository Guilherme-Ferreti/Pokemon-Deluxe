<?php

declare(strict_types=1);

use App\Shop\Actions\GetTodaysTmRotationAction;
use Shared\Models\Move;
use Shared\Models\TmRotation;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\travel;

it('creates one rotation per day', function () {
    Move::factory()->plainDamage()->count(12)->create();

    $firstRotation  = new GetTodaysTmRotationAction()->handle();
    $secondRotation = new GetTodaysTmRotationAction()->handle();

    assertDatabaseCount(TmRotation::class, 1);

    expect($secondRotation->id)->toBe($firstRotation->id)
        ->and($secondRotation->move_ids)->toBe($firstRotation->move_ids)
        ->and($firstRotation->date->isToday())->toBeTrue();
});

it('picks 10 different tms that have a price', function () {
    $sellableTms  = Move::factory()->plainDamage()->count(12)->create();
    $zMoves       = Move::factory()->plainDamage()->count(5)->create(['is_z_move' => true]);
    $dynamaxMoves = Move::factory()->plainDamage()->count(5)->create(['is_dynamax_move' => true]);

    $rotation = new GetTodaysTmRotationAction()->handle();

    expect($rotation->move_ids)->toHaveCount(10)
        ->and(array_unique($rotation->move_ids))->toHaveCount(10)
        ->and(array_diff($rotation->move_ids, $sellableTms->modelKeys()))->toBeEmpty()
        ->and(array_intersect($rotation->move_ids, [...$zMoves->modelKeys(), ...$dynamaxMoves->modelKeys()]))->toBeEmpty();
});

it('creates a new rotation on a new day', function () {
    Move::factory()->plainDamage()->count(12)->create();

    $todaysRotation = new GetTodaysTmRotationAction()->handle();

    travel(1)->days();

    $tomorrowsRotation = new GetTodaysTmRotationAction()->handle();

    assertDatabaseCount(TmRotation::class, 2);

    expect($tomorrowsRotation->id)->not->toBe($todaysRotation->id)
        ->and($tomorrowsRotation->date->isToday())->toBeTrue();
});
