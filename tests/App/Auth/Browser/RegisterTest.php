<?php

declare(strict_types=1);

use Tests\App\Auth\RequestFactories\StoreRegisterRequestFactory;

it('loads the register page without errors', function () {
    visit(route('web.auth.register.create'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@auth-form-title', 'Create an account');
});

it('registers a new user', function () {
    $data = StoreRegisterRequestFactory::new()->create();

    visit(route('web.auth.register.create'))
        ->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('password_confirmation', $data['password'])
        ->press('@register-button')
        ->assertPathIs(route('web.dashboard.home', absolute: false));
});

it('shows validation errors on registration', function () {
    $data = StoreRegisterRequestFactory::new()->withExistingEmail()->create();

    visit(route('web.auth.register.create'))
        ->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('password_confirmation', $data['password'])
        ->press('@register-button')
        ->assertVisible('@error-message');
});
