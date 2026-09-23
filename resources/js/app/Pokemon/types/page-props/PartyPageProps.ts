import type { Pokemon } from '@/shared/types/models';

export interface PartyPageProps {
  party: {
    id: Pokemon['id'];
    name: string;
    frontSprite: string | null;
    level: Pokemon['level'];
  }[];
}
