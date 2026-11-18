import type {
  Ailment,
  BattleLogKind,
  BattleStatus,
  BattleType,
} from '@/shared/types/enums';
import type { Move, Pokemon } from '@/shared/types/response';
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
  team: {
    name: Pokemon['name'];
    isFainted: boolean;
  }[];
};

export type BattleTeamMember = {
  name: Pokemon['name'];
  portrait: string | null;
  gender: Pokemon['gender'];
  level: Pokemon['level'];
  boost: number;
  currentHp: number;
  maxHp: number;
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

export type BattleShowPageProps = {
  battle: {
    type: BattleType;
    scene: BattleScene;
    status: BattleStatus;
    turn: number;
    log: BattleLogTurn[];
    isAwaitingSendOut: boolean;
  };
  player: Omit<BattleCombatant, 'team'> & {
    team: BattleTeamMember[];
  };
  opponent: BattleCombatant;
  moves: Pick<Move, 'id' | 'name' | 'type' | 'isZMove' | 'isDynamaxMove'>[];
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
