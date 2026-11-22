import type { PokemonGender, PokemonType } from '@/shared/types/enums';
import type { ExploreArea } from './ExploreIndexPageProps';

export type ExploreEncounter = {
  sprite: string;
  name: string;
  gender: PokemonGender;
  level: number;
  types: [PokemonType, PokemonType?];
  isShiny: boolean;
  isLegendary: boolean;
  isMythical: boolean;
};

export type ExploreShowPageProps = {
  area: ExploreArea;
  encounter: ExploreEncounter | null;
  isBattleInProgress: boolean;
};
