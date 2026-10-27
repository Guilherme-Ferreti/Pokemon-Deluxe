import type { IncubatorStatus } from '@/app/Pokemon/types/enums/IncubatorStatus';
import type { Incubation, Pokemon } from '@/shared/types/response';
import type { Pokeball } from '@/shared/types/response/Pokeball';

export type Incubator = {
  index: Incubation['incubatorIndex'];
  status: IncubatorStatus;
  incubationStartedAt: Incubation['createdAt'] | null;
  readyToHatchAt: Incubation['readyToHatchAt'] | null;
  pokemon: Pick<Pokemon, 'name' | 'frontSprite' | 'gender'> | null;
};

export type NurseryPageProps = {
  incubators: Incubator[];
  pokeballs: {
    id: Pokeball['id'];
    name: Pokeball['name'];
    sprite: Pokeball['sprite'];
    quantity: number;
  }[];
};
