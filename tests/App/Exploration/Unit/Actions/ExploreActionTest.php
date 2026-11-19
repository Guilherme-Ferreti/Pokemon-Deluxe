<?php

declare(strict_types=1);

use App\Battle\Exceptions\BattleAlreadyInProgressException;
use App\Exploration\Actions\ExploreAction;
use Shared\Enums\Area;
use Shared\Enums\BattleStatus;
use Shared\Enums\PokemonGender;
use Shared\Enums\PokemonGenderRate;
use Shared\Enums\PokemonType;
use Shared\Helpers\RandomHelper;
use Shared\Models\Battle;
use Shared\Models\Encounter;
use Shared\Models\Species;
use Shared\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('saves a rolled wild pokemon of the area as the player encounter', function () {
    $user     = User::factory()->create();
    $caterpie = Species::factory()->create([
        'types'           => [PokemonType::BUG],
        'evolution_stage' => 1,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'gender_rate'     => PokemonGenderRate::EQUAL,
        'wild_min_level'  => 2,
        'wild_max_level'  => 12,
    ]);
    Species::factory()->create(['types' => [PokemonType::FIRE]]);

    RandomHelper::fake([1, $caterpie, 7, true, false]);

    $encounter = new ExploreAction()->handle($user, Area::FOREST);

    expect($encounter->user_id)->toBe($user->id)
        ->and($encounter->area)->toBe(Area::FOREST)
        ->and($encounter->species_id)->toBe($caterpie->id)
        ->and($encounter->level)->toBe(7)
        ->and($encounter->is_shiny)->toBeTrue()
        ->and($encounter->gender)->toBe(PokemonGender::FEMALE);
    assertDatabaseCount(Encounter::class, 1);
    assertDatabaseHas(Encounter::class, ['user_id' => $user->id, 'species_id' => $caterpie->id, 'level' => 7]);
});

it('replaces the encounter the player already had', function () {
    $user     = User::factory()->create();
    $previous = Encounter::factory()->for($user)->create(['area' => Area::COAST]);
    $caterpie = Species::factory()->create([
        'types'           => [PokemonType::BUG],
        'evolution_stage' => 1,
        'is_legendary'    => false,
        'is_mythical'     => false,
        'gender_rate'     => PokemonGenderRate::GENDERLESS,
        'wild_min_level'  => 2,
        'wild_max_level'  => 12,
    ]);

    RandomHelper::fake([1, $caterpie, 4, false]);

    new ExploreAction()->handle($user, Area::FOREST);

    assertDatabaseCount(Encounter::class, 1);
    assertDatabaseHas(Encounter::class, ['id' => $previous->id, 'user_id' => $user->id, 'area' => Area::FOREST->value, 'species_id' => $caterpie->id, 'level' => 4]);
});

it('refuses to explore while a battle is ongoing', function () {
    $user = User::factory()->create();
    Battle::factory()->for($user)->create(['status' => BattleStatus::ONGOING]);

    expect(fn () => new ExploreAction()->handle($user, Area::FOREST))->toThrow(BattleAlreadyInProgressException::class);

    assertDatabaseCount(Encounter::class, 0);
});
