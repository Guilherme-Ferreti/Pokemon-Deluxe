import type {
  ElementalType,
  FilterOption,
  Generation,
  PaginatedList,
  PokemonGender,
  PokemonTrait,
  SearchBoxSortBy,
  SortDirection,
  SpeciesTypes,
} from '@/shared/types';

export interface BoxIndexPageProps {
  pokemonList: PaginatedList<{
    id: string;
    name: string;
    gender: PokemonGender;
    frontSprite: string | null;
    level: number;
    experience: number;
    species: {
      types: SpeciesTypes;
      slug: string;
    };
    moves: {
      name: string;
    }[];
  }>;
  activeFilters: {
    search: string;
    types: ElementalType[];
    traits: PokemonTrait[];
    generations: Generation[];
    sort: SearchBoxSortBy;
    dir: SortDirection;
  };
  filterOptions: {
    types: FilterOption<ElementalType>[];
    traits: FilterOption<PokemonTrait>[];
    generations: FilterOption<Generation>[];
    sort: FilterOption<SearchBoxSortBy>[];
    dir: FilterOption<SortDirection>[];
  };
}
