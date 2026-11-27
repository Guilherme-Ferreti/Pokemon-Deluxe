<?php

declare(strict_types=1);

namespace App\Missions\Exceptions;

use Illuminate\Support\Arr;
use Shared\Base\DomainException;

final class RewardOverflowException extends DomainException
{
    /**
     * @param  list<string>  $overflowingItems
     */
    public function __construct(public readonly array $overflowingItems)
    {
        parent::__construct('Your inventory is too full to hold ' . Arr::join($overflowingItems, ', ', ' and ') . '.');
    }

    public function getKey(): string
    {
        return 'reward';
    }
}
