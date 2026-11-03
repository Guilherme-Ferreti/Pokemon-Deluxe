import type { FilterOption, PaginatedList } from '@/shared/types';
import type {
  Ailment,
  PokemonType,
  MoveTrait,
  SortDirection,
} from '@/shared/types/enums';
import type { Move } from '@/shared/types/response';
import type { SearchAttackdexSortBy } from '../enums/SearchAttackdexSortBy';

export type AttackdexIndexPageProps = {
  moves: PaginatedList<{
    id: Move['id'];
    name: Move['name'];
    type: Move['type'];
    category: Move['category'];
    power: Move['power'];
    accuracy: Move['accuracy'];
    ailment: Move['ailment'];
    ailmentChance: Move['ailmentChance'];
    flinchChance: Move['flinchChance'];
    isZMove: Move['isZMove'];
    isDynamaxMove: Move['isDynamaxMove'];
  }>;
  filters: {
    options: {
      types: FilterOption<PokemonType>[];
      ailments: FilterOption<Ailment>[];
      traits: FilterOption<MoveTrait>[];
    };
    active: {
      search: string;
      types: PokemonType[];
      ailments: Ailment[];
      traits: MoveTrait[];
      sort: SearchAttackdexSortBy;
      dir: SortDirection;
    };
  };
};
