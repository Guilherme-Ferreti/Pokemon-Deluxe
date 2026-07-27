<?php

declare(strict_types=1);

use Shared\Models\User;
use Tests\App\Auth\RequestFactories\StoreRegisterRequestFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('an user can register', function () {
    $data = StoreRegisterRequestFactory::new()->create();

    post(route('web.auth.register.store'), $data)
        ->assertRedirectToRoute('web.dashboard.home');

    assertDatabaseHas(User::class, [
        'name'  => $data['name'],
        'email' => $data['email'],
    ]);
});

test('an user cannot register with an existing email', function () {
    $data = StoreRegisterRequestFactory::new()->withExistingEmail()->create();

    post(route('web.auth.register.store'), $data)
        ->assertRedirectBackWithErrors('email');
});
