import type { EggIncubationStatus } from '@/shared/types/enums';
import type { EggIncubation, Pokemon } from '@/shared/types/response';

export interface NurseryPageProps {
  activeEggIncubations: {
    id: EggIncubation['id'];
    eggIncubatorIndex: EggIncubation['eggIncubatorIndex'];
    readyToHatchAt: EggIncubation['readyToHatchAt'];
    status: EggIncubationStatus;
    pokemon: {
      name: Pokemon['name'];
      frontSprite: Pokemon['frontSprite'];
      gender: EggIncubation['gender'];
    } | null;
    createdAt: EggIncubation['createdAt'];
  }[];
  incubatorAvailability: {
    inUse: EggIncubation['eggIncubatorIndex'][];
    idle: EggIncubation['eggIncubatorIndex'][];
    locked: EggIncubation['eggIncubatorIndex'][];
  };
}
