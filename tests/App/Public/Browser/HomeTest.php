<?php

declare(strict_types=1);

it('loads the public home page without errors', function () {
    visit(route('public.web.home'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSee('Pokémon Deluxe')
        ->assertSeeIn('@home__feature-list', 'Explore areas and catch Pokémon');
});

it('navigates to the login page', function (string $link) {
    visit(route('public.web.home'))
        ->click("@{$link}")
        ->assertPathIs(route('auth.web.login.create', absolute: false));
})->with(['home__header-login-link', 'home__hero-login-link']);

it('navigates to the registration page', function (string $link) {
    visit(route('public.web.home'))
        ->click("@{$link}")
        ->assertPathIs(route('auth.web.registration.create', absolute: false));
})->with(['home__header-registration-link', 'home__hero-registration-link']);
