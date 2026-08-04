<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('web.pokemon.party'))
        ->assertRedirectToRoute('web.auth.login.create');
});

it('loads the dashboard page with expected structure', function () {
    actingAs(User::factory()->create())
        ->get(route('web.dashboard.home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Dashboard')
        );
});
