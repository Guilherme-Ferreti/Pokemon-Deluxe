<?php

declare(strict_types=1);

it('loads the public home page without errors', function () {
    visit(route('public.web.home'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSee('Pokémon Deluxe');
});

it('navigates to the login page', function () {
    visit(route('public.web.home'))
        ->click('Login')
        ->assertPathIs(route('auth.web.login.create', absolute: false));
});

it('navigates to the register page', function () {
    visit(route('public.web.home'))
        ->click('Register')
        ->assertPathIs(route('auth.web.register.create', absolute: false));
});
