<?php

declare(strict_types=1);

namespace App\Battle\Exceptions;

use Shared\Base\DomainException;
use Shared\Models\Move;

final class OncePerBattleMoveAlreadyUsedException extends DomainException
{
    public function __construct(Move $move)
    {
        parent::__construct("{$move->name} can only be used once per battle.");
    }

    public function getKey(): string
    {
        return 'move';
    }
}
