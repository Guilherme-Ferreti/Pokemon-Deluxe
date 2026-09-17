<?php

declare(strict_types=1);

namespace App\Dex\Queries;

use Illuminate\Support\Str;
use Shared\Models\Species;

final class NextSpeciesQuery
{
    public function handle(Species $species): ?Species
    {
        /** @var literal-string $orderByClause */
        $orderByClause = $this->orderByClause();

        $subquery = Species::selectRaw("id, LEAD(id) OVER (ORDER BY $orderByClause) as next_id");

        $nextIdQuery = Species::query()
            ->fromSub($subquery, 'ordered_species')
            ->select('next_id')
            ->where('id', $species->id)
            ->limit(1);

        return Species::query()
            ->select('id', 'slug', 'name', 'pokedex_number', 'portraits')
            ->firstWhere('id', $nextIdQuery);
    }

    private function orderByClause(): string
    {
        return Str::of(Species::query()->orderByPokedexNumber()->toSql())
            ->lower()
            ->after('order by')
            ->toString();
    }
}
