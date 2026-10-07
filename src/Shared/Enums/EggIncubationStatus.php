<?php

declare(strict_types=1);

namespace Shared\Enums;

use Shared\Models\EggIncubation;

enum EggIncubationStatus: string
{
    case INCUBATING       = 'incubating';
    case READY_TO_HATCH   = 'ready-to-hatch';
    case READY_TO_CAPTURE = 'ready-to-capture';
    case COMPLETED        = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::INCUBATING       => 'Incubating',
            self::READY_TO_HATCH   => 'Ready to hatch',
            self::READY_TO_CAPTURE => 'Ready to capture',
            self::COMPLETED        => 'Completed',
        };
    }

    public static function fromModel(EggIncubation $eggIncubation): self
    {
        if ($eggIncubation->captured_at !== null) {
            return self::COMPLETED;
        }

        if ($eggIncubation->ready_to_hatch_at->isFuture()) {
            return self::INCUBATING;
        }

        if ($eggIncubation->hatched_at === null) {
            return self::READY_TO_HATCH;
        }

        return self::READY_TO_CAPTURE;
    }
}
