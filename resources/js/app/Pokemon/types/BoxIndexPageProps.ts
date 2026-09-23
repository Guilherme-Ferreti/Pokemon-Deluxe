import type { SearchBoxSortBy } from '@/app/Pokemon/types/enums/SearchBoxSortBy';
import type {
  ElementalType,
  FilterOption,
  Generation,
  Move,
  PaginatedList,
  Pokemon,
  PokemonTrait,
  SortDirection,
  Species,
} from '@/shared/types';

export interface BoxIndexPageProps {
  pokemonList: PaginatedList<{
    id: Pokemon['id'];
    name: string;
    gender: Pokemon['gender'];
    frontSprite: string;
    level: Pokemon['level'];
    experience: Pokemon['experience'];
    species: {
      types: Species['types'];
      slug: Species['slug'];
    };
    moves: {
      name: Move['name'];
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
