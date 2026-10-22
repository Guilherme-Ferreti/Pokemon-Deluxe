import type { EggIncubationStatus } from '@/shared/types/enums';
import type { EggIncubation, Pokemon } from '@/shared/types/response';

export type NurseryPageProps = {
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
    active: EggIncubation['eggIncubatorIndex'][];
    idle: EggIncubation['eggIncubatorIndex'][];
    notPurchased: EggIncubation['eggIncubatorIndex'][];
  };
};
