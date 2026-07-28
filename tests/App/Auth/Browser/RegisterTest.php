<?php

declare(strict_types=1);

namespace Tests\App\Auth\Browser;

use Tests\App\Auth\RequestFactories\StoreRegisterRequestFactory;

it('registers an user', function () {
    $data = StoreRegisterRequestFactory::new()->create();

    $page = visit(route('web.auth.register.create'));

    $page->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSee('Create an account');

    $page->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('password_confirmation', $data['password'])
        ->press('@register-button')
        ->assertPathIs(route('web.dashboard.home', absolute: false));
});

it('displays validation errors', function () {
    $data = StoreRegisterRequestFactory::new()->withExistingEmail()->create();

    $page = visit(route('web.auth.register.create'));

    $page->fill('name', $data['name'])
        ->fill('email', $data['email'])
        ->fill('password', $data['password'])
        ->fill('password_confirmation', $data['password'])
        ->press('@register-button')
        ->assertVisible('@error-message');
});
