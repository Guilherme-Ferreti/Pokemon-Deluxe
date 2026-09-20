import type {
  Ailment,
  ElementalType,
  FilterOption,
  MoveCategory,
  PaginatedList,
  MoveTrait,
  SearchAttackdexSortBy,
  SortDirection,
} from '@/shared/types';

export interface AttackdexIndexPageProps {
  moves: PaginatedList<{
    id: string;
    name: string;
    type: ElementalType;
    category: MoveCategory;
    power: number | null;
    accuracy: number;
    ailment: Ailment | null;
    ailmentChance: number | null;
    flinchChance: number | null;
    isZMove: boolean;
    isDynamaxMove: boolean;
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
