<?php

declare(strict_types=1);

namespace App\Pokemon\Enums;

use Shared\Enums\IncubationStatus;

enum IncubatorStatus: string
{
    case NOT_PURCHASED  = 'not-purchased';
    case IDLE           = 'idle';
    case INCUBATING     = 'incubating';
    case READY_TO_HATCH = 'ready-to-hatch';
    case READY_TO_CATCH = 'ready-to-catch';

    public static function fromIncubationStatus(IncubationStatus $incubationStatus): self
    {
        return match ($incubationStatus) {
            IncubationStatus::INCUBATING     => self::INCUBATING,
            IncubationStatus::READY_TO_HATCH => self::READY_TO_HATCH,
            IncubationStatus::READY_TO_CATCH => self::READY_TO_CATCH,
            IncubationStatus::COMPLETED      => self::IDLE,
        };
    }
}
