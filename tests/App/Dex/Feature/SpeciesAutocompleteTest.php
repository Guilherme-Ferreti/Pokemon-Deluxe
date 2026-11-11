<?php

declare(strict_types=1);

use Shared\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

it('throttles excessive autocomplete requests', function () {
    actingAs(User::factory()->create());

    foreach (range(1, 60) as $attempt) {
        getJson(route('dex.api.species.autocomplete', ['search' => 'pika']))
            ->assertSuccessful();
    }

    getJson(route('dex.api.species.autocomplete', ['search' => 'pika']))
        ->assertTooManyRequests();
});
