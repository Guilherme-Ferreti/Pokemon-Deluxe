<?php

declare(strict_types=1);

namespace App\Economy\Actions;

use App\Economy\Exceptions\NotEnoughMoneyException;
use InvalidArgumentException;
use Shared\Models\User;

final class SpendMoneyAction
{
    private User $user;

    private int $amount;

    public function handle(User $user, int $amount): void
    {
        $this->user   = $user;
        $this->amount = $amount;

        $this->ensureAmountIsPositive();

        $isSpent = $this->decrementMoneyIfEnough();

        throw_unless($isSpent, fn () => new NotEnoughMoneyException($this->amount, $this->user->money));
    }

    private function ensureAmountIsPositive(): void
    {
        throw_if($this->amount < 1, InvalidArgumentException::class, "The amount must be at least 1, [{$this->amount}] given.");
    }

    private function decrementMoneyIfEnough(): bool
    {
        $affectedRows = User::query()
            ->whereKey($this->user->id)
            ->where('money', '>=', $this->amount)
            ->decrement('money', $this->amount);

        return $affectedRows > 0;
    }
}
