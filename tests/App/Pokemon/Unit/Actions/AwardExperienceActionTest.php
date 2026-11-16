<?php

declare(strict_types=1);

use App\Pokemon\Actions\AwardExperienceAction;
use App\Pokemon\Events\PokemonLevelledUp;
use Illuminate\Support\Facades\Event;
use Shared\Models\Pokemon;

it('adds the XP and levels the pokemon up, stopping at level 100', function (int $level, int $experience, int $expectedExperience, int $expectedLevel) {
    $pokemon = Pokemon::factory()->create(['level' => $level]);

    new AwardExperienceAction()->handle($pokemon, $experience);

    expect($pokemon->fresh())
        ->experience->toBe($expectedExperience)
        ->level->toBe($expectedLevel);
})->with([
    'within the level'        => [50, 500, 50_500, 50],
    'up one level'            => [50, 1_500, 51_500, 51],
    'up several levels'       => [50, 3_000, 53_000, 53],
    'up to level 100'         => [99, 5_000, 100_000, 100],
    'level 100 gains nothing' => [100, 500, 100_000, 100],
]);

it('refuses negative XP', function () {
    $pokemon = Pokemon::factory()->create(['level' => 50]);

    expect(fn () => new AwardExperienceAction()->handle($pokemon, -1))
        ->toThrow(InvalidArgumentException::class)
        ->and($pokemon->fresh()?->experience)->toBe(50_000);
});

it('announces a level up', function () {
    Event::fake([PokemonLevelledUp::class]);

    $pokemon = Pokemon::factory()->create(['level' => 50]);

    new AwardExperienceAction()->handle($pokemon, 2_000);

    Event::assertDispatchedTimes(PokemonLevelledUp::class, 1);
    Event::assertDispatched(PokemonLevelledUp::class, fn (PokemonLevelledUp $event) => $event->pokemonId === $pokemon->id
        && $event->userId === $pokemon->user_id
        && $event->levelBefore === 50
        && $event->levelAfter === 52);
});

it('announces nothing when the level stays the same', function (int $level, int $experience) {
    Event::fake([PokemonLevelledUp::class]);

    $pokemon = Pokemon::factory()->create(['level' => $level]);

    new AwardExperienceAction()->handle($pokemon, $experience);

    Event::assertNotDispatched(PokemonLevelledUp::class);
})->with([
    'within the level' => [50, 500],
    'at level 100'     => [100, 500],
]);
