import type { SearchPokedexSortBy } from '@/app/Dex/types/enums/SearchPokedexSortBy';
import type { FilterOption, PaginatedList } from '@/shared/types';
import type {
  ElementalType,
  Generation,
  SortDirection,
  SpeciesTrait,
} from '@/shared/types/enums';
import type { Species } from '@/shared/types/models';

export interface PokedexIndexPageProps {
  speciesList: PaginatedList<{
    id: Species['id'];
    portrait: Species['portraits']['regular'];
    name: Species['name'];
    pokedexNumber: Species['pokedexNumber'];
    types: Species['types'];
    generation: Species['generation'];
    slug: Species['slug'];
    regularInBox: boolean;
    shinyInBox: boolean;
  }>;
  activeFilters: {
    search: string;
    types: ElementalType[];
    traits: SpeciesTrait[];
    generations: Generation[];
    sort: SearchPokedexSortBy;
    dir: SortDirection;
  };
  filterOptions: {
    types: FilterOption<ElementalType>[];
    traits: FilterOption<SpeciesTrait>[];
    generations: FilterOption<Generation>[];
  };
}
