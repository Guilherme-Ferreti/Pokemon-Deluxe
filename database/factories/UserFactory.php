<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Shared\Helpers\AppHelper;
use Shared\Models\User;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    private static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'                        => fake()->name(),
            'email'                       => fake()->unique()->safeEmail(),
            'email_verified_at'           => now(),
            'password'                    => self::$password ??= Hash::make('password'),
            'remember_token'              => Str::random(10),
            'purchased_incubator_indexes' => Arr::take(AppHelper::allIncubatorIndexes(), fake()->numberBetween(1, config('game.user.max_incubators'))),
            'money'                       => 0,
        ];
    }

    public function withMoney(int $money): static
    {
        return $this->state(fn () => [
            'money' => $money,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}
