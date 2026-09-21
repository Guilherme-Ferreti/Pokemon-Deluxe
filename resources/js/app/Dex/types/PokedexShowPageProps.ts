import type {
  ElementalType,
  MoveCategory,
  PokemonGender,
  SpeciesSprites,
  SpeciesTypes,
} from '@/shared/types';

export interface PokedexShowPageProps {
  species: {
    id: string;
    name: string;
    types: SpeciesTypes;
    pokedexNumber: number;
    genus: string;
    textEntry: string;
    sprites: SpeciesSprites;
    portraits: {
      regular: string | null;
      shiny: string | null;
    };
    traits: {
      isBaby: boolean;
      isLegendary: boolean;
      isMythical: boolean;
      isMega: boolean;
      isGmax: boolean;
    };
    generation: string;
    slug: string;
    isCapturable: boolean;
    captureRatePercentage: number;
    isGenderless: boolean;
    malePercentage: number;
    femalePercentage: number;
  };
  previousSpecies: {
    name: string;
    pokedexNumber: number;
    portrait: string;
    slug: string;
  } | null;
  nextSpecies: {
    name: string;
    pokedexNumber: number;
    portrait: string;
    slug: string;
  } | null;
  captureStatistics: {
    currentUser: {
      regular: number;
      shiny: number;
    };
    global: {
      regular: number;
      shiny: number;
    };
  };
  damageTaken: Record<ElementalType, number>;
  evolutionChain: {
    previous: {
      species: {
        name: string;
        slug: string;
        portrait: string;
      };
      evolutionItem: {
        name: string;
        sprite: string | null;
      } | null;
      minLevel: number | null;
      gender: PokemonGender | null;
    }[];
    into: {
      species: {
        name: string;
        slug: string;
        portrait: string;
      };
      evolutionItem: {
        name: string;
        sprite: string | null;
      } | null;
      minLevel: number | null;
      gender: PokemonGender | null;
    }[];
  };
  learnableMoves: {
    levelUp: {
      name: string;
      type: ElementalType;
      category: MoveCategory;
      power: number | null;
      accuracy: number;
      isZMove: boolean;
      isDynamaxMove: boolean;
      levelLearn: number;
    }[];
    tm: {
      name: string;
      type: ElementalType;
      category: MoveCategory;
      power: number | null;
      accuracy: number;
      isZMove: boolean;
      isDynamaxMove: boolean;
      tmCode: string;
    }[];
  };
}
