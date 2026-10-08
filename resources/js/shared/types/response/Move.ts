import type { Ailment } from '@/shared/types/enums/Ailment';
import type { ElementalType } from '@/shared/types/enums/ElementalType';
import type { Generation } from '@/shared/types/enums/Generation';
import type { MoveCategory } from '@/shared/types/enums/MoveCategory';
import type { MoveDamageClass } from '@/shared/types/enums/MoveDamageClass';

export type Move = {
  id: string;
  slug: string;
  name: string;
  type: ElementalType;
  category: MoveCategory;
  damageClass: MoveDamageClass;
  power: number | null;
  accuracy: number;
  pp: number;
  ailment: Ailment | null;
  ailmentChance: number | null;
  flinchChance: number | null;
  minHits: number | null;
  maxHits: number | null;
  isZMove: boolean;
  isDynamaxMove: boolean;
  generation: Generation;
  tmCode: string;
};
