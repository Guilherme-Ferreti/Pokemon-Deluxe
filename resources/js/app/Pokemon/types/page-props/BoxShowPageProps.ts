import type {
  MoveCategory,
  PokemonGender,
  PokemonType,
} from '@/shared/types/enums';
import type { Species } from '@/shared/types/response';
import type { PartyMember } from '../PartyMember';

export type PokemonMove = {
  id: string;
  name: string;
  type: PokemonType;
  power: number | null;
  category: MoveCategory;
  isZMove: boolean;
  isDynamaxMove: boolean;
};

export type PokemonKnownMove = PokemonMove & { slot: number | null };

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
    displayName: string;
    portrait: string | null;
  };
  minLevel: number | null;
  gender: PokemonGender | null;
  evolutionItem: {
    id: string;
    name: string;
    sprite: string | null;
  } | null;
  isAllowed: boolean;
};

export type BoxShowPageProps = {
  pokemon: {
    id: string;
    name: string;
    frontSprite: string;
    portrait: string | null;
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
    isLocked: boolean;
    inParty: boolean;
  };
  stats: {
    hp: PokemonStat;
    attack: PokemonStat;
    damageTaken: PokemonStat;
  };
  knownMoves: PokemonKnownMove[];
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
  };
  party: PartyMember[];
  maxPartySize: number;
};
