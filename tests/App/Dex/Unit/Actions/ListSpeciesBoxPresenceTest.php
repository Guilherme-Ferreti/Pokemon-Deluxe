<?php

declare(strict_types=1);

use App\Dex\Actions\ListSpeciesBoxPresence;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

it('marks a species as present when the user owns it', function () {
    $user           = User::factory()->create();
    $species        = Species::factory()->create();
    $missingSpecies = Species::factory()->create();

    Pokemon::factory()->for($user)->for($species)->regular()->create();

    $presence = new ListSpeciesBoxPresence()->handle($user, collect([$species->id, $missingSpecies->id]));

    expect($presence->all())->toBe([
        ['speciesId' => $species->id, 'regularInBox' => true, 'shinyInBox' => false],
        ['speciesId' => $missingSpecies->id, 'regularInBox' => false, 'shinyInBox' => false],
    ]);
});

it('marks a species as shiny in box when the user owns a shiny form', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    Pokemon::factory()->for($user)->for($species)->shiny()->create();

    $presence = new ListSpeciesBoxPresence()->handle($user, collect([$species->id]));

    expect($presence->first())->toBe([
        'speciesId'    => $species->id,
        'regularInBox' => false,
        'shinyInBox'   => true,
    ]);
});

it('marks both flags when the user owns both a regular and a shiny form', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    Pokemon::factory()->for($user)->for($species)->regular()->create();
    Pokemon::factory()->for($user)->for($species)->shiny()->create();

    $presence = new ListSpeciesBoxPresence()->handle($user, collect([$species->id]));

    expect($presence->first())->toBe([
        'speciesId'    => $species->id,
        'regularInBox' => true,
        'shinyInBox'   => true,
    ]);
});

it('does not mark a species owned by another user', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();
    $species   = Species::factory()->create();

    Pokemon::factory()->for($otherUser)->for($species)->regular()->create();

    $presence = new ListSpeciesBoxPresence()->handle($user, collect([$species->id]));

    expect($presence->first())->toBe([
        'speciesId'    => $species->id,
        'regularInBox' => false,
        'shinyInBox'   => false,
    ]);
});
