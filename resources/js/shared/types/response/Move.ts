import type { Ailment } from '@/shared/types/enums/Ailment';
import type { MoveCategory } from '@/shared/types/enums/MoveCategory';
import type { PokemonType } from '@/shared/types/enums/PokemonType';

export type Move = {
  id: string;
  name: string;
  type: PokemonType;
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
