<?php

declare(strict_types=1);

use Shared\Models\User;
use Tests\App\Auth\RequestFactories\StoreLoginRequestFactory;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\post;

test('an user can login', function () {
    $data = StoreLoginRequestFactory::new()->create();

    post(route('web.auth.login.store'), $data)
        ->assertRedirectToRoute('web.dashboard.home');

    assertAuthenticatedAs(User::firstWhere('email', $data['email']));
});

test('an user cannot login with invalid credentials', function () {
    $data = StoreLoginRequestFactory::new()->withInvalidCredentials()->create();

    post(route('web.auth.login.store'), $data)
        ->assertRedirectBackWithErrors('email');

    assertGuest();
});
