<?php

declare(strict_types=1);

namespace App\Economy\Exceptions;

use Shared\Base\DomainException;

final class NotEnoughMoneyException extends DomainException
{
    public function __construct(int $amount, int $money)
    {
        parent::__construct('You need ₽ ' . number_format($amount) . ' but only have ₽ ' . number_format($money) . '.');
    }

    public function getKey(): string
    {
        return 'money';
    }
}
