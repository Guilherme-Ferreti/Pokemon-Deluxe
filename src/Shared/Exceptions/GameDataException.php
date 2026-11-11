<?php

declare(strict_types=1);

namespace Shared\Exceptions;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Shared\Models\Species;

final class GameDataException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $context
     */
    private function __construct(string $message, private readonly array $context = [])
    {
        parent::__construct($message);
    }

    public static function speciesWithoutBaseMoves(Species $species): self
    {
        return new self("Species [{$species->slug}] has no base moves.", [
            'species_id'   => $species->id,
            'species_slug' => $species->slug,
        ]);
    }

    public function report(): void
    {
        Log::channel('game-data')->error($this->getMessage(), [
            ...$this->context,
            'exception' => $this,
        ]);
    }
}
