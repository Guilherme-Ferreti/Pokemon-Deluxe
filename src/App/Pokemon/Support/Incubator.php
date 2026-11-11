<?php

declare(strict_types=1);

namespace App\Pokemon\Support;

use App\Pokemon\Enums\IncubatorStatus;
use Shared\Models\Incubation;
use Shared\ValueObjects\IncubatorIndex;

final readonly class Incubator
{
    public function __construct(
        public IncubatorIndex $index,
        public bool $isPurchased,
        public ?Incubation $incubation,
    ) {}

    public function status(): IncubatorStatus
    {
        if ($this->incubation instanceof Incubation) {
            return IncubatorStatus::fromIncubationStatus($this->incubation->getStatus());
        }

        return $this->isPurchased ? IncubatorStatus::IDLE : IncubatorStatus::NOT_PURCHASED;
    }

    public function isIdle(): bool
    {
        return $this->status() === IncubatorStatus::IDLE;
    }

    public function isActive(): bool
    {
        return $this->isPurchased && $this->incubation instanceof Incubation;
    }
}
