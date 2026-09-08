<?php

declare(strict_types=1);

use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the dashboard page without errors', function () {
    actingAs(User::factory()->create());

    visit(route('dashboard.web.home'))
        ->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSeeIn('@page-header__title', 'Dashboard');
});
