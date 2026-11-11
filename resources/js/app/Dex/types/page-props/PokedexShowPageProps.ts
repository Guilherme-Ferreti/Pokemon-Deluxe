import type { PokemonType, PokemonGender } from '@/shared/types/enums';
import type { Move, Species } from '@/shared/types/response';

export type PokedexShowPageProps = {
  species: {
    name: Species['name'];
    slug: Species['slug'];
  };
  hero: Hero;
  overview: Overview;
  previousSpecies: NavigationItem;
  nextSpecies: NavigationItem;
  catchStatistics: {
    currentUser: {
      regular: number;
      shiny: number;
    };
    global: {
      regular: number;
      shiny: number;
    };
  };
  damageTaken: Record<PokemonType, number>;
  evolutionChain: {
    previous: EvolutionChainNodes;
    into: EvolutionChainNodes;
  };
  baseMoves: BaseMove[];
};

type Hero = {
  name: Species['name'];
  sprites: {
    frontDefault: Species['sprites']['frontDefault'];
    frontShiny: Species['sprites']['frontShiny'];
    frontFemale: Species['sprites']['frontFemale'];
    frontShinyFemale: Species['sprites']['frontShinyFemale'];
  };
  pokedexNumber: Species['pokedexNumber'];
  genus: Species['genus'];
  textEntry: Species['textEntry'];
};

type Overview = {
  types: Species['types'];
  portraits: {
    regular: Species['portraits']['regular'];
    shiny: Species['portraits']['shiny'];
  };
  traits: {
    isBaby: Species['isBaby'];
    isLegendary: Species['isLegendary'];
    isMythical: Species['isMythical'];
    isMega: Species['isMega'];
    isGmax: Species['isGmax'];
  };
  generation: Species['generation'];
  catchRatePercentage: number;
  isGenderless: boolean;
  malePercentage: number;
  femalePercentage: number;
};

type NavigationItem = {
  name: Species['name'];
  pokedexNumber: Species['pokedexNumber'];
  portrait: Species['portraits']['regular'];
  slug: Species['slug'];
} | null;

type EvolutionChainNodes = {
  species: {
    name: Species['name'];
    portrait: Species['portraits']['regular'];
    slug: Species['slug'];
  };
  evolutionItem: {
    name: string;
    sprite: string | null;
  } | null;
  minLevel: number | null;
  gender: PokemonGender | null;
}[];

export type BaseMove = {
  name: Move['name'];
  type: Move['type'];
  category: Move['category'];
  power: Move['power'];
  accuracy: Move['accuracy'];
  ailment: Move['ailment'];
  ailmentChance: Move['ailmentChance'];
  flinchChance: Move['flinchChance'];
  isZMove: Move['isZMove'];
  isDynamaxMove: Move['isDynamaxMove'];
};
