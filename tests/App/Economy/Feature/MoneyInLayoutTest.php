<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function () {
    get(route('dashboard.web.home'))
        ->assertRedirectToRoute('auth.web.login.create');
});

it('shares the money of the logged in user', function () {
    actingAs(User::factory()->withMoney(12450)->create())
        ->get(route('dashboard.web.home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.money', 12450)
        );
});
