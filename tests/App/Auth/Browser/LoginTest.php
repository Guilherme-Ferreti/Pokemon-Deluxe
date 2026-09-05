<?php

declare(strict_types=1);

use Tests\App\Auth\RequestFactories\StoreLoginRequestFactory;

it('loads the login page without errors', function () {
    visit(route('auth.web.login.create'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@auth-form-title', 'Login to your account');
});

it('logs in a user', function () {
    $data = StoreLoginRequestFactory::new()->create();

    visit(route('auth.web.login.create'))
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->press('@login-button')
        ->assertPathIs(route('dashboard.web.home', absolute: false));
});

it('shows validation errors on invalid login', function () {
    $data = StoreLoginRequestFactory::new()->withInvalidCredentials()->create();

    visit(route('auth.web.login.create'))
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->press('@login-button')
        ->assertVisible('@error-message');
});
