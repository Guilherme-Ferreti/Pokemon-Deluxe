import type { Ailment, BattleType } from '@/shared/types/enums';
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

export type BattleShowPageProps = {
  battle: {
    type: BattleType;
    turn: number;
    log: string[];
  };
  player: BattleCombatant & {
    currentHp: number;
    maxHp: number;
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
};
