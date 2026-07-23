<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use Shared\Models\User;

final class RegisterUser
{
    public function handle(string $name, string $email, string $password): User
    {
        return User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
        ]);
    }
}
