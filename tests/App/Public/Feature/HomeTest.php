<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('loads the public home page with expected structure', function () {
    get(route('public.web.home'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home')
        );
});
