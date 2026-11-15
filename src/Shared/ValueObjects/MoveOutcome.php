<?php

declare(strict_types=1);

namespace Shared\ValueObjects;

use InvalidArgumentException;
use Shared\Enums\Ailment;
use Shared\Interfaces\ValueObject;

final readonly class MoveOutcome implements ValueObject
{
    /**
     * @param  list<MoveHit>  $hits
     */
    public function __construct(
        public bool $hasHit,
        public array $hits,
        public bool $causesFlinch,
        public ?Ailment $inflictedAilment,
    ) {
        throw_if(! $hasHit && ($hits !== [] || $causesFlinch || $inflictedAilment instanceof Ailment), InvalidArgumentException::class, 'A missed move cannot deal hits, cause flinch or inflict an ailment.');

        throw_if($inflictedAilment === Ailment::UNKNOWN, InvalidArgumentException::class, 'The inflicted ailment must be a known ailment.');
    }

    public static function miss(): self
    {
        return new self(hasHit: false, hits: [], causesFlinch: false, inflictedAilment: null);
    }

    public function hitCount(): int
    {
        return count($this->hits);
    }

    public function equals(ValueObject $other): bool
    {
        if (! $other instanceof self
            || $this->hasHit !== $other->hasHit
            || $this->hitCount() !== $other->hitCount()
            || $this->causesFlinch !== $other->causesFlinch
            || $this->inflictedAilment !== $other->inflictedAilment) {
            return false;
        }

        foreach ($this->hits as $index => $hit) {
            if (! $hit->equals($other->hits[$index])) {
                return false;
            }
        }

        return true;
    }
}
