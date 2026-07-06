<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use Illuminate\Support\Facades\Auth;

final class LoginUser
{
    public function handle(string $email, string $password, bool $remember = false): bool
    {
        $credentials = [
            'email'    => $email,
            'password' => $password,
        ];

        if (! Auth::attempt($credentials, $remember)) {
            return false;
        }

        request()->session()->regenerate();

        return true;
    }
}
