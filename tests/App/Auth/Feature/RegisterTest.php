<?php

declare(strict_types=1);

use Shared\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

test('an user can register', function () {
    $data = [
        'name'                  => 'John Doe',
        'email'                 => 'john@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ];

    post(route('web.auth.register.store'), $data)
        ->assertRedirectToRoute('web.dashboard.home');

    assertDatabaseHas(User::class, [
        'name'  => $data['name'],
        'email' => $data['email'],
    ]);
});

test('an user cannot register with an existing email', function () {
    User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $data = [
        'name'                  => 'John Doe',
        'email'                 => 'john@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ];

    post(route('web.auth.register.store'), $data)
        ->assertRedirectBackWithErrors('email');
});
