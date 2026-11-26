import type {
  MoveCategory,
  PokemonGender,
  PokemonType,
} from '@/shared/types/enums';
import type { Species } from '@/shared/types/response';

export type PokemonMove = {
  id: string;
  name: string;
  type: PokemonType;
  power: number | null;
  category: MoveCategory;
  isZMove: boolean;
  isDynamaxMove: boolean;
};

export type PokemonStat = {
  value: number;
  base: number;
  boostMultiplier: number;
  shinyMultiplier: number | null;
};

export type PokemonEvolutionOption = {
  id: string;
  species: {
    id: string;
    slug: string;
    name: string;
    portrait: string | null;
    types: Species['types'];
  };
  minLevel: number | null;
  gender: PokemonGender | null;
  evolutionItem: {
    id: string;
    name: string;
    sprite: string | null;
    isOwned: boolean;
  } | null;
  isAllowed: boolean;
  unmetRequirement: string | null;
};

export type PokemonShowPageProps = {
  pokemon: {
    id: string;
    name: string;
    frontSprite: string;
    species: {
      id: string;
      slug: string;
      name: string;
      types: Species['types'];
    };
    gender: PokemonGender;
    isShiny: boolean;
    level: number;
    boost: number;
    experience: { current: number; needed: number } | null;
    pokeball: { name: string; sprite: string | null };
    caughtAt: string;
    originalTrainer: string | null;
    isLocked: boolean;
    inParty: boolean;
    partySize: number;
  };
  stats: {
    hp: PokemonStat;
    attack: PokemonStat;
    damageTaken: PokemonStat;
  };
  activeMoves: { slot: number; move: PokemonMove }[];
  knownMoves: (PokemonMove & { slot: number | null })[];
  evolutionOptions: PokemonEvolutionOption[];
  tms: (PokemonMove & {
    tmName: string;
    quantity: number;
    isKnown: boolean;
    needsMaxLevel: boolean;
  })[];
  boostStones: {
    quantity: number;
    failureChance: number | null;
    maxBoost: number;
    safeUntil: number;
  };
};
