<?php

declare(strict_types=1);

use App\Auth\Actions\ListLayoutSprites;
use Illuminate\Support\Lottery;
use Shared\Models\Species;
use Shared\ValueObjects\SpeciesSprites;

afterEach(function () {
    Lottery::determineResultsNormally();
});

it('returns species default sprite when the lottery loses', function () {
    $species   = Species::factory()->count(3)->create();
    $frontUrls = $species->map(fn (Species $species) => $species->sprites->front_default)->all();

    Lottery::alwaysLose();

    $sprites = new ListLayoutSprites()->handle();

    expect($sprites)->toHaveCount(3)
        ->and($sprites)->toEqualCanonicalizing($frontUrls);
});

it('returns species shiny sprite when the lottery wins', function () {
    $species   = Species::factory()->count(3)->create();
    $frontUrls = $species->map(fn (Species $species) => $species->sprites->front_shiny)->all();

    Lottery::alwaysWin();

    $sprites = new ListLayoutSprites()->handle();

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

    Lottery::alwaysLose();

    $sprites = new ListLayoutSprites()->handle();

    expect($sprites)->toHaveCount($species->count())
        ->and($sprites)->toEqualCanonicalizing(
            $species->map(fn (Species $species) => $species->sprites->front_default)->all()
        );
});

it('limits the returned sprites to 49', function () {
    Species::factory()->count(60)->create(['starter_moves_ids' => []]);

    $sprites = new ListLayoutSprites()->handle();

    expect($sprites)->toHaveCount(49);
});
