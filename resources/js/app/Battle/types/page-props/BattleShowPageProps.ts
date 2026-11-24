import type {
  Ailment,
  Area,
  BattleLogKind,
  BattleStatus,
  BattleType,
} from '@/shared/types/enums';
import type { Move, Pokemon, Species } from '@/shared/types/response';
import type { BattleScene } from '../enums/BattleScene';

export type BattleCombatant = {
  name: Pokemon['name'];
  gender: Pokemon['gender'];
  level: Pokemon['level'];
  boost: number;
  isShiny: boolean;
  ailment: Exclude<Ailment, Ailment.CONFUSION | Ailment.UNKNOWN> | null;
  sprite: Pokemon['frontSprite'];
  currentHp: number;
  maxHp: number;
};

export type BattleTeamMemberMove = {
  name: Move['name'];
  type: Move['type'];
  power: number | null;
};

export type BattleStatBonuses = {
  hp: number;
  attack: number;
  damageTaken: number;
};

export type BattlePokemonDetails = {
  name: Pokemon['name'];
  portrait: string | null;
  gender: Pokemon['gender'];
  level: Pokemon['level'];
  boost: number;
  isShiny: boolean;
  statBonuses: BattleStatBonuses;
  types: Species['types'];
  moves: BattleTeamMemberMove[];
  currentHp: number;
  maxHp: number;
};

export type BattleOpponentTeamMember =
  | (BattlePokemonDetails & {
      isFainted: boolean;
      isActive: boolean;
      isRevealed: true;
    })
  | ({
      [Key in keyof BattlePokemonDetails]: null;
    } & {
      isFainted: false;
      isActive: false;
      isRevealed: false;
    });

export type BattleTeamMember = BattlePokemonDetails & {
  ailment: BattleCombatant['ailment'];
  isFainted: boolean;
  isActive: boolean;
};

export type BattleLogTurn = {
  turn: number;
  isEnd: boolean;
  entries: {
    kind: BattleLogKind;
    text: string;
    moveName: string | null;
  }[];
};

export type BattlePokeball = {
  slug: string;
  name: string;
  sprite: string | null;
  quantity: number;
  multiplier: number;
  isBoosted: boolean;
};

export type BattleShowPageProps = {
  battle: {
    type: BattleType;
    scene: BattleScene;
    status: BattleStatus;
    area: Area | null;
    turn: number;
    log: BattleLogTurn[];
    isAwaitingSendOut: boolean;
  };
  player: BattleCombatant & {
    team: BattleTeamMember[];
  };
  opponent: BattleCombatant & {
    team: BattleOpponentTeamMember[];
  };
  moves: Pick<Move, 'id' | 'name' | 'type' | 'isZMove' | 'isDynamaxMove'>[];
  pokeballs: BattlePokeball[];
  result: {
    experienceGains: {
      name: Pokemon['name'];
      portrait: string | null;
      gender: Pokemon['gender'];
      earnedExperience: number;
      levelBefore: number;
      levelAfter: number;
      isFromExperienceShare: boolean;
      isAtMaxLevel: boolean;
    }[];
    caughtPokemon: {
      name: Pokemon['name'];
      pokeballName: string;
      isInParty: boolean;
    } | null;
  } | null;
};
