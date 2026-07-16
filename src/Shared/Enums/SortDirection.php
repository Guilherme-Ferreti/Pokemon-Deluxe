<?php

declare(strict_types=1);

namespace Shared\Enums;

enum SortDirection: string
{
    case ASC  = 'asc';
    case DESC = 'desc';

    public function label(): string
    {
        return match ($this) {
            self::ASC  => 'Ascending',
            self::DESC => 'Descending',
        };
    }
}
