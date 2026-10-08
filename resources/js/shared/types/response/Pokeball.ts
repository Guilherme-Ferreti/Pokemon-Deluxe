import type { PokeballType } from '@/shared/types/enums/PokeballType';

export type Pokeball = {
  id: string;
  slug: string;
  name: string;
  description: string;
  sprite: string | null;
  type: PokeballType;
  captureRate: number;
};
