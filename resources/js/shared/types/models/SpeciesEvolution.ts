import type { PokemonGender } from '@/shared/types/enums/PokemonGender';

export type SpeciesEvolution = {
  id: string;
  fromSpeciesId: string;
  intoSpeciesId: string;
  evolutionItemId: string | null;
  minLevel: number | null;
  gender: PokemonGender | null;
};
