<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\User;
use Tests\App\Auth\RequestFactories\StoreLoginRequestFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('redirects authenticated users to dashboard', function () {
    actingAs(User::factory()->create())
        ->get(route('web.auth.login.create'))
        ->assertRedirectToRoute('web.dashboard.home');
});

it('loads the login page with expected structure', function () {
    get(route('web.auth.login.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
        );
});

it('logs in a user', function () {
    $data = StoreLoginRequestFactory::new()->create();

    post(route('web.auth.login.store'), $data)
        ->assertRedirectToRoute('web.dashboard.home');

    assertAuthenticatedAs(User::firstWhere('email', $data['email']));
});

it('rejects login with invalid credentials', function () {
    $data = StoreLoginRequestFactory::new()->withInvalidCredentials()->create();

    post(route('web.auth.login.store'), $data)
        ->assertRedirectBackWithErrors('email');

    assertGuest();
});
