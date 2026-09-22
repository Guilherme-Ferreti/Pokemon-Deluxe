import type { ElementalType } from '@/shared/types/enums/ElementalType';
import type { Generation } from '@/shared/types/enums/Generation';

export type Species = {
  id: string;
  slug: string;
  name: string;
  types: [ElementalType, ElementalType?];
  pokedexNumber: number;
  genus: string;
  textEntry: string;
  sprites: {
    backDefault: string | null;
    backFemale: string | null;
    backShiny: string | null;
    backShinyFemale: string | null;
    frontDefault: string | null;
    frontFemale: string | null;
    frontShiny: string | null;
    frontShinyFemale: string | null;
  };
  portraits: {
    regular: string | null;
    shiny: string | null;
  };
  isBaby: boolean;
  isLegendary: boolean;
  isMythical: boolean;
  isMega: boolean;
  isGmax: boolean;
  generation: Generation;
  captureRate: number | null;
  genderRate: number;
};
