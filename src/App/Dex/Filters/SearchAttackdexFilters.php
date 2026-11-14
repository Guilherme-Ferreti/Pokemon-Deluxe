<?php

declare(strict_types=1);

namespace App\Dex\Filters;

use App\Dex\Enums\SearchAttackdexSortBy;
use Illuminate\Http\Request;
use Shared\Enums\Ailment;
use Shared\Enums\MoveTrait;
use Shared\Enums\PokemonType;
use Shared\Enums\SortDirection;
use Shared\Interfaces\Filter;
use Shared\ValueObjects\FilterOption;

final class SearchAttackdexFilters implements Filter
{
    public function __construct(
        public ?string $search,

        /** @var list<PokemonType> */
        public array $types,

        /** @var list<Ailment> */
        public array $ailments,

        /** @var list<MoveTrait> */
        public array $traits,

        public SearchAttackdexSortBy $sort,

        public SortDirection $dir,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(...[
            'search'   => $request->query('search'),
            'types'    => $request->collect('types')->toUniqueEnums(PokemonType::class)->all(),
            'ailments' => $request->collect('ailments')->toUniqueEnums(Ailment::class)->all(),
            'traits'   => $request->collect('traits')->toUniqueEnums(MoveTrait::class)->all(),
            'sort'     => $request->enum('sort', SearchAttackdexSortBy::class, SearchAttackdexSortBy::NAME),
            'dir'      => $request->enum('dir', SortDirection::class, SortDirection::ASC),
        ]);
    }

    /**
     * @return array<string, FilterOption[]>
     */
    public static function filterOptions(): array
    {
        return [
            'types'    => FilterOption::listFromEnum(PokemonType::class),
            'ailments' => FilterOption::listFromEnum(Ailment::class),
            'traits'   => FilterOption::listFromEnum(MoveTrait::class),
        ];
    }
}
