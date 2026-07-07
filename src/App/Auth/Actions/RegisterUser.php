<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use Shared\Models\User;

final class RegisterUser
{
    public function handle(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
        ]);

        new LoginUser()->handle($email, $password);

        return $user;
    }
}
