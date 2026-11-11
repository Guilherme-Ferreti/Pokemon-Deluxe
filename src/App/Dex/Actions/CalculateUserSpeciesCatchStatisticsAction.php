<?php

declare(strict_types=1);

namespace App\Dex\Actions;

use Shared\Models\Species;
use Shared\Models\User;

final class CalculateUserSpeciesCatchStatisticsAction
{
    /**
     * @return array{regular: int, shiny:int}
     */
    public function handle(User $user, Species $species): array
    {
        $counts = $species
            ->pokemon()
            ->whereBelongsTo($user)
            ->toBase()
            ->selectRaw('coalesce(sum(case when is_shiny then 0 else 1 end), 0) as regular')
            ->selectRaw('coalesce(sum(case when is_shiny then 1 else 0 end), 0) as shiny')
            ->first();

        return [
            'regular' => (int) $counts->regular,
            'shiny'   => (int) $counts->shiny,
        ];
    }
}
