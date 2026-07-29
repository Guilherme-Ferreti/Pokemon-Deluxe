<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the dashboard', function () {
    actingAs(User::factory()->create())
        ->get(route('web.dashboard.home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Dashboard')
        );
});
