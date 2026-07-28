<?php

declare(strict_types=1);

namespace Tests\App\Auth\Browser;

use Tests\App\Auth\RequestFactories\StoreLoginRequestFactory;

it('logs in an user', function () {
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

it('displays validation errors', function () {
    $data = StoreLoginRequestFactory::new()->withInvalidCredentials()->create();

    $page = visit(route('web.auth.login.create'));

    $page->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->press('@login-button')
        ->assertVisible('@error-message');
});
