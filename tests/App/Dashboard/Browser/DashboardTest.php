<?php

declare(strict_types=1);

use Shared\Models\User;

use function Pest\Laravel\actingAs;

it('loads the dashboard', function () {
    actingAs(User::factory()->create());

    $page = visit(route('web.dashboard.home'));

    $page->assertNoSmoke()
        ->assertNoAccessibilityIssues()
        ->assertSee('Dashboard');
});
