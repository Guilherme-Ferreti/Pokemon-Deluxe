<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;
use Shared\Models\User;
use Tests\App\Auth\RequestFactories\StoreRegisterRequestFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('redirects authenticated users to dashboard', function () {
    actingAs(User::factory()->create())
        ->get(route('auth.web.login.create'))
        ->assertRedirectToRoute('dashboard.web.home');
});

it('loads the register page with expected structure', function () {
    get(route('auth.web.register.create'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/pages/Register')
        );
});

it('registers a new user', function () {
    $data = StoreRegisterRequestFactory::new()->create();

    post(route('auth.web.register.store'), $data)
        ->assertRedirectToRoute('dashboard.web.home');

    assertDatabaseHas(User::class, [
        'name'  => $data['name'],
        'email' => $data['email'],
    ]);
});

it('rejects registration with an existing email', function () {
    $data = StoreRegisterRequestFactory::new()->withExistingEmail()->create();

    post(route('auth.web.register.store'), $data)
        ->assertRedirectBackWithErrors('email');
});
