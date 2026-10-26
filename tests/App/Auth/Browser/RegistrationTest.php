<?php

declare(strict_types=1);

use Tests\App\Auth\RequestFactories\StoreRegistrationRequestFactory;

it('loads the registration page without errors', function () {
    visit(route('auth.web.registration.create'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@auth-form__title', 'Create an account');
});

it('registers a new user', function () {
    $data = StoreRegistrationRequestFactory::new()->create();

    visit(route('auth.web.registration.create'))
        ->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('passwordConfirmation', $data['password'])
        ->press('@auth-form__register-button')
        ->assertPathIs(route('dashboard.web.home', absolute: false));
});

it('shows validation errors on registration', function () {
    $data = StoreRegistrationRequestFactory::new()->withExistingEmail()->create();

    visit(route('auth.web.registration.create'))
        ->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('passwordConfirmation', $data['password'])
        ->press('@auth-form__register-button')
        ->assertVisible('@error-message');
});
