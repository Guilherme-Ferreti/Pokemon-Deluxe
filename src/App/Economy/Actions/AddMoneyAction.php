<?php

declare(strict_types=1);

namespace App\Economy\Actions;

use InvalidArgumentException;
use Shared\Models\User;

final class AddMoneyAction
{
    private User $user;

    private int $amount;

    public function handle(User $user, int $amount): void
    {
        $this->user   = $user;
        $this->amount = $amount;

        $this->ensureAmountIsPositive();

        User::query()->whereKey($this->user->id)->increment('money', $this->amount);
    }

    private function ensureAmountIsPositive(): void
    {
        throw_if($this->amount < 1, InvalidArgumentException::class, "The amount must be at least 1, [{$this->amount}] given.");
    }
}
