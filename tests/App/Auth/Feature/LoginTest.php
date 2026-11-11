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
        ->get(route('auth.web.login.create'))
        ->assertRedirectToRoute('dashboard.web.home');
});

it('loads the login page with expected structure', function () {
    get(route('auth.web.login.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/pages/Login/index')
        );
});

it('logs in a user', function () {
    $data = StoreLoginRequestFactory::new()->create();

    post(route('auth.web.login.store'), $data)
        ->assertRedirectToRoute('dashboard.web.home');

    assertAuthenticatedAs(User::firstWhere('email', $data['email']));
});

it('rejects login with invalid credentials', function () {
    $data = StoreLoginRequestFactory::new()->withInvalidCredentials()->create();

    post(route('auth.web.login.store'), $data)
        ->assertRedirectBackWithErrors('email');

    assertGuest();
});

it('throttles repeated failed login attempts for the same email', function () {
    $data = StoreLoginRequestFactory::new()->withInvalidCredentials()->create();

    foreach (range(1, 5) as $attempt) {
        post(route('auth.web.login.store'), $data)
            ->assertRedirectBackWithErrors('email');
    }

    post(route('auth.web.login.store'), $data)
        ->assertRedirectBackWithoutErrors()
        ->assertInertiaFlash('toast.type', 'warning')
        ->assertInertiaFlash('toast.title', 'Too many attempts');

    assertGuest();
});
