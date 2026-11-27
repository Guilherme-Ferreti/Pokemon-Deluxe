import type { FilterOption, PaginatedList } from '@/shared/types';
import type {
  PokemonType,
  Generation,
  SortDirection,
} from '@/shared/types/enums';
import type { Move, Pokemon, Species } from '@/shared/types/response';
import type { PokemonTrait } from '../enums/PokemonTrait';
import type { SearchBoxSortBy } from '../enums/SearchBoxSortBy';
import type { PartyMember } from '../PartyMember';

export type BoxIndexPageProps = {
  pokemonList: PaginatedList<{
    id: Pokemon['id'];
    name: Pokemon['name'];
    gender: Pokemon['gender'];
    frontSprite: Pokemon['frontSprite'];
    level: Pokemon['level'];
    experience: Pokemon['experience'];
    inParty: boolean;
    species: {
      types: Species['types'];
      slug: Species['slug'];
    };
    moves: {
      name: Move['name'];
    }[];
  }>;
  party: PartyMember[];
  maxPartySize: number;
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
