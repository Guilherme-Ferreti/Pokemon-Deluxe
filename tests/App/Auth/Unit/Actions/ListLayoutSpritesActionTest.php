<?php

declare(strict_types=1);

use App\Auth\Actions\ListLayoutSpritesAction;
use Shared\Helpers\RandomHelper;
use Shared\Models\Species;
use Shared\ValueObjects\SpeciesSprites;

it('returns species default sprite when the shiny roll fails', function () {
    $species   = Species::factory()->count(3)->create();
    $frontUrls = $species->map(fn (Species $species) => $species->sprites->front_default)->all();

    RandomHelper::fake([false, false, false]);

    $sprites = new ListLayoutSpritesAction()->handle();

    expect($sprites)->toHaveCount(3)
        ->and($sprites)->toEqualCanonicalizing($frontUrls);
});

it('returns species shiny sprite when the shiny roll succeeds', function () {
    $species   = Species::factory()->count(3)->create();
    $frontUrls = $species->map(fn (Species $species) => $species->sprites->front_shiny)->all();

    RandomHelper::fake([true, true, true]);

    $sprites = new ListLayoutSpritesAction()->handle();

    expect($sprites)->toHaveCount(3)
        ->and($sprites)->toEqualCanonicalizing($frontUrls);
});

it('excludes species without a front default or shiny sprite', function () {
    $species = Species::factory()->count(2)->create();
    Species::factory()->create([
        'sprites' => new SpeciesSprites(
            back_default: null,
            back_female: null,
            back_shiny: null,
            back_shiny_female: null,
            front_default: null,
            front_female: null,
            front_shiny: null,
            front_shiny_female: null,
        ),
    ]);

    RandomHelper::fake([false, false]);

    $sprites = new ListLayoutSpritesAction()->handle();

    expect($sprites)->toHaveCount($species->count())
        ->and($sprites)->toEqualCanonicalizing(
            $species->map(fn (Species $species) => $species->sprites->front_default)->all()
        );
});

it('limits the returned sprites to 49', function () {
    Species::factory()->count(60)->create();

    $sprites = new ListLayoutSpritesAction()->handle();

    expect($sprites)->toHaveCount(49);
});
