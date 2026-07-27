<?php

declare(strict_types=1);

namespace Tests\App\Auth\Browser;

use Shared\Models\User;

it('registers an user', function () {
    $page = visit(route('web.auth.register.create'));

    $page->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSee('Create an account');

    $page->fill('name', 'John Doe')
        ->fill('email', 'john@example.com')
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->press('@register-button')
        ->assertPathIs(route('web.dashboard.home', absolute: false));
});

it('displays validation errors', function () {
    $page = visit(route('web.auth.register.create'));

    $user = User::factory()->create();

    $page->fill('name', 'John Doe')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->press('@register-button')
        ->assertVisible('@error-message');
});
