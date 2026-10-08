import type { SearchBoxSortBy } from '@/app/Pokemon/types/enums/SearchBoxSortBy';
import type { FilterOption, PaginatedList } from '@/shared/types';
import type {
  ElementalType,
  Generation,
  PokemonTrait,
  SortDirection,
} from '@/shared/types/enums';
import type { Move, Pokemon, Species } from '@/shared/types/response';

export interface BoxIndexPageProps {
  pokemonList: PaginatedList<{
    id: Pokemon['id'];
    name: Pokemon['name'];
    gender: Pokemon['gender'];
    frontSprite: Pokemon['frontSprite'];
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
