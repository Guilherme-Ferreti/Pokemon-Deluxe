<?php

declare(strict_types=1);

use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;

it('logs out the authenticated user', function () {
    actingAs(User::factory()->create());

    $page = visit(route('web.dashboard.home'));

    $page->press('@sidebar-user-menu-button')
        ->press('@logout-button')
        ->assertPathIs(route('web.public.home', absolute: false));

    assertGuest();
});
