import type { Incubation, Pokemon } from '@/shared/types/response';
import type { Pokeball } from '@/shared/types/response/Pokeball';
import type { IncubatorStatus } from '../enums/IncubatorStatus';

export type Incubator = {
  index: Incubation['incubatorIndex'];
  status: IncubatorStatus;
  incubationStartedAt: Incubation['createdAt'] | null;
  readyToHatchAt: Incubation['readyToHatchAt'] | null;
  pokemon: Pick<Pokemon, 'name' | 'frontSprite' | 'gender'> | null;
};

export type NurseryPageProps = {
  incubators: Incubator[];
  nextIncubatorPrice: number;
  pokeballs: {
    id: Pokeball['id'];
    name: Pokeball['name'];
    sprite: Pokeball['sprite'];
    quantity: number;
  }[];
};
