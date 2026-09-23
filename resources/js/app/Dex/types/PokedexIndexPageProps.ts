import type { SearchPokedexSortBy } from '@/app/Dex/types/enums/SearchPokedexSortBy';
import type {
  ElementalType,
  FilterOption,
  Generation,
  PaginatedList,
  SortDirection,
  Species,
  SpeciesTrait,
} from '@/shared/types';

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
