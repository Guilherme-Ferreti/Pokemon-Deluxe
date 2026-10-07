import type { PokemonGender } from '@/shared/types/enums/PokemonGender';

export type EggIncubation = {
  id: string;
  userId: string;
  eggIncubatorIndex: number;
  speciesId: string;
  gender: PokemonGender;
  isShiny: boolean;
  readyToHatchAt: string;
  hatchedAt: string | null;
  capturedAt: string | null;
  createdAt: string | null;
  updatedAt: string | null;
};
