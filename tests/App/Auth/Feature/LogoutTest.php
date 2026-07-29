<?php

declare(strict_types=1);

use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;

test('an user can logout', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('web.auth.logout'))
        ->assertRedirectToRoute('web.public.home');

    assertGuest();
});
