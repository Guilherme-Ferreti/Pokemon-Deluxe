import type { SearchAttackdexSortBy } from '@/app/Dex/types/enums/SearchAttackdexSortBy';
import type {
  Ailment,
  ElementalType,
  FilterOption,
  PaginatedList,
  MoveTrait,
  SortDirection,
  Move,
} from '@/shared/types';

export interface AttackdexIndexPageProps {
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
  activeFilters: {
    search: string;
    types: ElementalType[];
    ailments: Ailment[];
    traits: MoveTrait[];
    sort: SearchAttackdexSortBy;
    dir: SortDirection;
  };
  filterOptions: {
    types: FilterOption<ElementalType>[];
    ailments: FilterOption<Ailment>[];
    traits: FilterOption<MoveTrait>[];
  };
}
