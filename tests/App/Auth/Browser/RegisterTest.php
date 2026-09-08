<?php

declare(strict_types=1);

use Tests\App\Auth\RequestFactories\StoreRegisterRequestFactory;

it('loads the register page without errors', function () {
    visit(route('auth.web.register.create'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@auth-form__title', 'Create an account');
});

it('registers a new user', function () {
    $data = StoreRegisterRequestFactory::new()->create();

    visit(route('auth.web.register.create'))
        ->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('password_confirmation', $data['password'])
        ->press('@auth-form__register-button')
        ->assertPathIs(route('dashboard.web.home', absolute: false));
});

it('shows validation errors on registration', function () {
    $data = StoreRegisterRequestFactory::new()->withExistingEmail()->create();

    visit(route('auth.web.register.create'))
        ->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('password_confirmation', $data['password'])
        ->press('@auth-form__register-button')
        ->assertVisible('@error-message');
});
