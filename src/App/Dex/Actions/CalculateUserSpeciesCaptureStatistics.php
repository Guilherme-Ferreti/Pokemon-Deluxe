<?php

declare(strict_types=1);

namespace App\Dex\Actions;

use Shared\Models\Species;
use Shared\Models\User;

final class CalculateUserSpeciesCaptureStatistics
{
    /**
     * @return array{regular: int, shiny:int}
     */
    public function handle(User $user, Species $species): array
    {
        return [
            'regular' => $species->pokemon()->regular()->whereBelongsTo($user)->count(),
            'shiny'   => $species->pokemon()->shiny()->whereBelongsTo($user)->count(),
        ];
    }
}
