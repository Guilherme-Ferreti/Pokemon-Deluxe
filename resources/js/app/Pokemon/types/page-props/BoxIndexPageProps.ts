import type { FilterOption, PaginatedList } from '@/shared/types';
import type {
  PokemonType,
  Generation,
  PokemonTrait,
  SortDirection,
} from '@/shared/types/enums';
import type { Move, Pokemon, Species } from '@/shared/types/response';
import type { SearchBoxSortBy } from '../enums/SearchBoxSortBy';

export type BoxIndexPageProps = {
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
  filters: {
    options: {
      types: FilterOption<PokemonType>[];
      traits: FilterOption<PokemonTrait>[];
      generations: FilterOption<Generation>[];
      sort: FilterOption<SearchBoxSortBy>[];
      dir: FilterOption<SortDirection>[];
    };
    active: {
      search: string;
      types: PokemonType[];
      traits: PokemonTrait[];
      generations: Generation[];
      sort: SearchBoxSortBy;
      dir: SortDirection;
    };
  };
};
