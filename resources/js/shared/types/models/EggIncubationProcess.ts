import type { PokemonGender } from '@/shared/types/enums/PokemonGender';

export type EggIncubationProcess = {
  id: string;
  eggIncubatorIndex: number;
  speciesId: string;
  gender: PokemonGender;
  isShiny: boolean;
  eggReadyToHatchAt: string;
  eggHatchedAt: string | null;
  eggCapturedAt: string | null;
  userId: string;
  createdAt: string | null;
  updatedAt: string | null;
};
