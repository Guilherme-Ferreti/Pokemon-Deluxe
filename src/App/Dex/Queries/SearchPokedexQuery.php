<?php

declare(strict_types=1);

namespace App\Dex\Queries;

use Illuminate\Database\Eloquent\Builder;
use Shared\Models\Species;

final class SearchPokedexQuery
{
    /**
     * @return Builder<Species>
     */
    public function handle(): Builder
    {
        return Species::query()
            ->orderBy('generation')
            ->orderBy('pokedex_number')
            ->orderByRaw('
                CASE
                    WHEN is_mega = 0 AND is_gmax = 0 THEN 1
                    WHEN is_mega = 1 AND is_gmax = 0 THEN 2
                    WHEN is_mega = 0 AND is_gmax = 1 THEN 3
                END
            ')
            ->orderBy('id');
    }
}
