<?php

declare(strict_types=1);

namespace Tests\App\Auth\Browser;

it('registers an user', function () {
    $page = visit(route('web.auth.register.create'));

    $page->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSee('Create an account');

    $page->fill('name', 'John Doe')
        ->fill('email', 'john@example.com')
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->press('Create account')
        ->assertPathIs(route('web.dashboard.home', absolute: false));
});
