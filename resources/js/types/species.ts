import type { ElementalType } from './php-types';

export type SpeciesTypes = [ElementalType] | [ElementalType, ElementalType];

export type SpeciesSprites = {
  backDefault: string | null;
  backFemale: string | null;
  backShiny: string | null;
  backShinyFemale: string | null;
  frontDefault: string | null;
  frontFemale: string | null;
  frontShiny: string | null;
  frontShinyFemale: string | null;
};
