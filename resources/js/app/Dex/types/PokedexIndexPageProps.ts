import type { SearchPokedexSortBy } from '@/app/Dex/types/enums/SearchPokedexSortBy';
import type {
  ElementalType,
  FilterOption,
  Generation,
  PaginatedList,
  SortDirection,
  SpeciesTrait,
  SpeciesTypes,
} from '@/shared/types';

export interface PokedexIndexPageProps {
  speciesList: PaginatedList<{
    id: string;
    portrait: string;
    name: string;
    pokedexNumber: number;
    types: SpeciesTypes;
    generation: Generation;
    slug: string;
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
