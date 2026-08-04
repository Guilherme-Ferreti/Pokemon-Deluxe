<?php

declare(strict_types=1);

it('loads the public home page without errors', function () {
    visit(route('web.public.home'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSee('Pokemon Deluxe');
});

it('navigates to the login page', function () {
    visit(route('web.public.home'))
        ->click('Login')
        ->assertPathIs(route('web.auth.login.create', absolute: false));
});

it('navigates to the register page', function () {
    visit(route('web.public.home'))
        ->click('Register')
        ->assertPathIs(route('web.auth.register.create', absolute: false));
});
