import type { ElementalType } from '@/shared/types/enums';

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
