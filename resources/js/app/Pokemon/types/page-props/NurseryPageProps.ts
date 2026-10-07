import type { EggIncubationStatus } from '@/shared/types/enums/EggIncubationStatus';
import type { EggIncubation } from '@/shared/types/models/EggIncubation';

export interface NurseryPageProps {
  activeEggIncubations: {
    id: EggIncubation['id'];
    eggIncubatorIndex: EggIncubation['eggIncubatorIndex'];
    readyToHatchAt: EggIncubation['readyToHatchAt'];
    status: EggIncubationStatus;
    pokemon: {
      name: string;
      frontSprite: string;
      gender: EggIncubation['gender'];
    } | null;
    createdAt: string;
  }[];
  incubatorAvailability: {
    inUse: EggIncubation['eggIncubatorIndex'][];
    idle: EggIncubation['eggIncubatorIndex'][];
    locked: EggIncubation['eggIncubatorIndex'][];
  };
}
