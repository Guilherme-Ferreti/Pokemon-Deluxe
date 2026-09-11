<?php

declare(strict_types=1);

namespace App\Dex\Filters;

use App\Dex\Enums\SearchAttackdexSortBy;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Shared\Enums\Ailment;
use Shared\Enums\ElementalType;
use Shared\Enums\SortDirection;
use Shared\Interfaces\Filter;
use Shared\ValueObjects\FilterOption;

final class SearchAttackdexFilters implements Filter
{
    public function __construct(
        public ?string $search,

        /** @var Collection<int, ElementalType> */
        public Collection $types,

        /** @var Collection<int, Ailment> */
        public Collection $ailments,

        public SearchAttackdexSortBy $sort,

        public SortDirection $dir,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(...[
            'search'   => $request->query('search'),
            'types'    => $request->collect('types')->toUniqueEnums(ElementalType::class),
            'ailments' => $request->collect('ailments')->toUniqueEnums(Ailment::class),
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
            'types'    => FilterOption::listFromEnum(ElementalType::class),
            'ailments' => FilterOption::listFromEnum(Ailment::class),
        ];
    }
}
