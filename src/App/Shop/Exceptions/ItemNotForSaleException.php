<?php

declare(strict_types=1);

namespace App\Shop\Exceptions;

use Shared\Base\DomainException;

final class ItemNotForSaleException extends DomainException
{
    public static function earnedOnly(string $itemName): self
    {
        return new self("{$itemName} isn't sold in the Shop.");
    }

    public static function notInTodaysTms(string $tmName): self
    {
        return new self("{$tmName} isn't for sale today.");
    }

    public function getKey(): string
    {
        return 'item';
    }
}
