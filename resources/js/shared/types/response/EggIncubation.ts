import type { PokemonGender } from '@/shared/types/enums/PokemonGender';

export type EggIncubation = {
  id: string;
  eggIncubatorIndex: number;
  gender: PokemonGender;
  readyToHatchAt: string;
  createdAt: string;
};
