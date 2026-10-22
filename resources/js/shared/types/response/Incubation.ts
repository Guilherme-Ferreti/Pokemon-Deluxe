import type { PokemonGender } from '@/shared/types/enums/PokemonGender';

export type Incubation = {
  id: string;
  incubatorIndex: number;
  gender: PokemonGender;
  readyToHatchAt: string;
  createdAt: string;
};
