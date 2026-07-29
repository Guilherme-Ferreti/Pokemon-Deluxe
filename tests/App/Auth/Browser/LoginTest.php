<?php

declare(strict_types=1);

use Tests\App\Auth\RequestFactories\StoreLoginRequestFactory;

it('logs in a user', function () {
    $data = StoreLoginRequestFactory::new()->create();

    $page = visit(route('web.auth.login.create'));

    $page->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSee('Login to your account');

    $page->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->press('@login-button')
        ->assertPathIs(route('web.dashboard.home', absolute: false));
});

it('shows validation errors on invalid login', function () {
    $data = StoreLoginRequestFactory::new()->withInvalidCredentials()->create();

    $page = visit(route('web.auth.login.create'));

    $page->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->press('@login-button')
        ->assertVisible('@error-message');
});
