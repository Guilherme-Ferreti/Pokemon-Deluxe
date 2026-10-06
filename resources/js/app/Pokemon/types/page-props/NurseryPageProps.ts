import type { EggIncubationProcessesStatus } from '@/shared/types/enums/EggIncubationProcessesStatus';
import type { EggIncubationProcess } from '@/shared/types/models/EggIncubationProcess';

export interface NurseryPageProps {
  activeEggIncubationProcesses: {
    eggIncubatorIndex: EggIncubationProcess['eggIncubatorIndex'];
    eggReadyToHatchAt: EggIncubationProcess['eggReadyToHatchAt'];
    status: EggIncubationProcessesStatus;
    pokemon: {
      name: string;
      frontSprite: string;
      gender: EggIncubationProcess['gender'];
    } | null;
    createdAt: string;
  }[];
  incubatorAvailability: {
    inUse: EggIncubationProcess['eggIncubatorIndex'][];
    idle: EggIncubationProcess['eggIncubatorIndex'][];
    locked: EggIncubationProcess['eggIncubatorIndex'][];
  };
}
