import type { Area, PokemonType } from '@/shared/types/enums';

export type ExploreArea = {
  slug: Area;
  label: string;
  types: [PokemonType, PokemonType];
};

export type ExploreIndexPageProps = {
  areas: ExploreArea[];
  isBattleInProgress: boolean;
};
