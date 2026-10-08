import type { Ailment } from '@/shared/types/enums/Ailment';
import type { ElementalType } from '@/shared/types/enums/ElementalType';
import type { MoveCategory } from '@/shared/types/enums/MoveCategory';

export type Move = {
  id: string;
  name: string;
  type: ElementalType;
  category: MoveCategory;
  power: number | null;
  accuracy: number;
  ailment: Ailment | null;
  ailmentChance: number | null;
  flinchChance: number | null;
  isZMove: boolean;
  isDynamaxMove: boolean;
  tmCode: string;
};
