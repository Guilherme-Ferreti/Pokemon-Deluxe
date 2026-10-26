<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\User;
use Tests\App\Auth\RequestFactories\StoreRegistrationRequestFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('redirects authenticated users to dashboard', function () {
    actingAs(User::factory()->create())
        ->get(route('auth.web.login.create'))
        ->assertRedirectToRoute('dashboard.web.home');
});

it('loads the registration page with expected structure', function () {
    get(route('auth.web.registration.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/pages/Registration')
        );
});

it('registers a new user', function () {
    $data = StoreRegistrationRequestFactory::new()->create();

    post(route('auth.web.registration.store'), $data)
        ->assertRedirectToRoute('dashboard.web.home');

    assertDatabaseCount(User::class, 1);

    assertDatabaseHas(User::class, [
        'name'  => $data['name'],
        'email' => $data['email'],
    ]);
});

it('rejects registration with an existing email', function () {
    $data = StoreRegistrationRequestFactory::new()->withExistingEmail()->create();

    post(route('auth.web.registration.store'), $data)
        ->assertRedirectBackWithErrors('email');
});

it('rejects registration if passwords do not match', function () {
    $data = StoreRegistrationRequestFactory::new()->withPasswordMismatch()->create();

    post(route('auth.web.registration.store'), $data)
        ->assertRedirectBackWithErrors('password');
});
