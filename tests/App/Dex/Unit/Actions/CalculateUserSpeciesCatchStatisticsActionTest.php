<?php

declare(strict_types=1);

use App\Dex\Actions\CalculateUserSpeciesCatchStatisticsAction;
use Shared\Models\Pokemon;
use Shared\Models\Species;
use Shared\Models\User;

it('counts regular and shiny catches of the user for the species', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    Pokemon::factory()->for($user)->for($species)->count(2)->regular()->create();
    Pokemon::factory()->for($user)->for($species)->count(1)->shiny()->create();

    $statistics = new CalculateUserSpeciesCatchStatisticsAction()->handle($user, $species);

    expect($statistics)->toBe(['regular' => 2, 'shiny' => 1]);
});

it('returns zero counts when the user has no catches', function () {
    $user    = User::factory()->create();
    $species = Species::factory()->create();

    $statistics = new CalculateUserSpeciesCatchStatisticsAction()->handle($user, $species);

    expect($statistics)->toBe(['regular' => 0, 'shiny' => 0]);
});

it('does not count catches belonging to another user', function () {
    $user      = User::factory()->create();
    $otherUser = User::factory()->create();
    $species   = Species::factory()->create();

    Pokemon::factory()->for($otherUser)->for($species)->count(3)->regular()->create();

    $statistics = new CalculateUserSpeciesCatchStatisticsAction()->handle($user, $species);

    expect($statistics)->toBe(['regular' => 0, 'shiny' => 0]);
});
