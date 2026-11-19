<?php

declare(strict_types=1);

namespace App\Exploration\Queries;

use Illuminate\Database\Eloquent\Builder;
use Shared\Enums\Area;
use Shared\Models\Species;

final class AreaSpeciesQuery
{
    /**
     * @return Builder<Species>
     */
    public function handle(Area $area): Builder
    {
        return Species::query()
            ->where(function (Builder $query) use ($area): void {
                foreach ($area->types() as $type) {
                    $query->orWhereJsonContains('types', $type->value);
                }
            })
            ->whereNotNull('wild_min_level')
            ->orderBy('pokedex_order');
    }
}
