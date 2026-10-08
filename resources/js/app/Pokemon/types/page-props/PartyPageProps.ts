import type { Pokemon } from '@/shared/types/response';

export interface PartyPageProps {
  party: {
    id: Pokemon['id'];
    name: Pokemon['name'];
    frontSprite: Pokemon['frontSprite'];
    level: Pokemon['level'];
  }[];
}
