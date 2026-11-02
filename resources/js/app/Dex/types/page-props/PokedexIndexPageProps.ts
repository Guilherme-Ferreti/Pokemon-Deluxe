import type { FilterOption, PaginatedList } from '@/shared/types';
import type {
  ElementalType,
  Generation,
  SortDirection,
  SpeciesTrait,
} from '@/shared/types/enums';
import type { Species } from '@/shared/types/response';
import type { SearchPokedexSortBy } from '../enums/SearchPokedexSortBy';

export type PokedexIndexPageProps = {
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
  filters: {
    options: {
      types: FilterOption<ElementalType>[];
      traits: FilterOption<SpeciesTrait>[];
      generations: FilterOption<Generation>[];
    };
    active: {
      search: string;
      types: ElementalType[];
      traits: SpeciesTrait[];
      generations: Generation[];
      sort: SearchPokedexSortBy;
      dir: SortDirection;
    };
  };
};
