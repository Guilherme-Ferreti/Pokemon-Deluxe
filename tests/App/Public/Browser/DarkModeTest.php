<?php

declare(strict_types=1);

it('applies the dark theme on first access when the user prefers dark', function () {
    visit(route('public.web.home'))
        ->inDarkMode()
        ->assertScript('document.documentElement.classList.contains("dark")', true);
});

it('does not apply the dark theme on first access when the user prefers light', function () {
    visit(route('public.web.home'))
        ->inLightMode()
        ->assertScript('document.documentElement.classList.contains("dark")', false);
});
