<?php

declare(strict_types=1);

use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;

it('logs out the authenticated user', function () {
    actingAs(User::factory()->create());

    visit(route('dashboard.web.home'))
        ->press('@sidebar-user-menu-button')
        ->press('@logout-button')
        ->assertPathIs(route('public.web.home', absolute: false));

    assertGuest();
});
