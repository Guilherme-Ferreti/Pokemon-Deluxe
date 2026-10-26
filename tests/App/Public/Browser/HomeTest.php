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

it('navigates to the registration page', function () {
    visit(route('public.web.home'))
        ->click('Sign Up')
        ->assertPathIs(route('auth.web.registration.create', absolute: false));
});
