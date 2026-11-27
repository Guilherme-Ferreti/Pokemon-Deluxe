<?php

declare(strict_types=1);

use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('shows the money in the sidebar footer', function () {
    actingAs(User::factory()->withMoney(12450)->create());

    visit(route('dashboard.web.home'))
        ->resize(1440, 900)
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@sidebar-nav-money', '₽ 12,450');
});

it('shows the money only in the sidebar on phones', function () {
    actingAs(User::factory()->withMoney(12450)->create());

    visit(route('dashboard.web.home'))
        ->resize(390, 844)
        ->assertDontSee('₽ 12,450')
        ->click('[data-sidebar="trigger"]')
        ->assertSeeIn('@sidebar-nav-money', '₽ 12,450');
});
