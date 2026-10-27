<?php

declare(strict_types=1);

namespace App\Dex\Actions;

use Shared\Models\Species;

final class CalculateSpeciesCatchStatisticsAction
{
    /**
     * @return array{regular: int, shiny:int}
     */
    public function handle(Species $species): array
    {
        return [
            'regular' => $species->pokemon()->regular()->count(),
            'shiny'   => $species->pokemon()->shiny()->count(),
        ];
    }
}
