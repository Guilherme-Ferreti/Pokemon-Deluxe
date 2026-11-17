import type {
  Ailment,
  BattleLogKind,
  BattleStatus,
  BattleType,
} from '@/shared/types/enums';
import type { Move, Pokemon } from '@/shared/types/response';

export type BattleCombatant = {
  name: Pokemon['name'];
  gender: Pokemon['gender'];
  level: Pokemon['level'];
  boost: number;
  isShiny: boolean;
  ailment: Exclude<Ailment, Ailment.CONFUSION | Ailment.UNKNOWN> | null;
  sprite: Pokemon['frontSprite'];
  team: {
    name: Pokemon['name'];
    isFainted: boolean;
  }[];
};

export type BattleTeamMember = {
  name: Pokemon['name'];
  portrait: string | null;
  level: Pokemon['level'];
  currentHp: number;
  maxHp: number;
  ailment: BattleCombatant['ailment'];
  isFainted: boolean;
  isActive: boolean;
};

export type BattleLogTurn = {
  turn: number;
  entries: {
    kind: BattleLogKind;
    text: string;
    moveName: string | null;
  }[];
};

export type BattleShowPageProps = {
  battle: {
    type: BattleType;
    status: BattleStatus;
    turn: number;
    log: BattleLogTurn[];
    isAwaitingSendOut: boolean;
  };
  player: Omit<BattleCombatant, 'team'> & {
    currentHp: number;
    maxHp: number;
    team: BattleTeamMember[];
  };
  opponent: BattleCombatant & {
    hpPercentage: number;
  };
  moves: (Pick<
    Move,
    'id' | 'name' | 'type' | 'power' | 'isZMove' | 'isDynamaxMove'
  > & {
    isSuperEffective: boolean;
  })[];
  result: {
    experienceGains: {
      name: Pokemon['name'];
      portrait: string | null;
      earnedExperience: number;
      levelBefore: number;
      levelAfter: number;
      experienceProgress: number | null;
    }[];
  } | null;
};
