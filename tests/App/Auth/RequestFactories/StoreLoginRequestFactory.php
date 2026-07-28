<?php

declare(strict_types=1);

namespace Tests\App\Auth\RequestFactories;

use App\Auth\Http\Web\Requests\StoreLoginRequest;
use Shared\Models\User;
use Worksome\RequestFactories\RequestFactory;

final class StoreLoginRequestFactory extends RequestFactory
{
    public static $factory = StoreLoginRequest::class;

    public function definition(): array
    {
        return [
            'email'    => User::factory()->create()->email,
            'password' => 'password',
            'remember' => false,
        ];
    }

    public function withInvalidCredentials(): static
    {
        return $this->state([
            'password' => 'wrong-password',
        ]);
    }
}
