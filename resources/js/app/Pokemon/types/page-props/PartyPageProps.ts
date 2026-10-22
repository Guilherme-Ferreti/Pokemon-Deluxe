import type { Pokemon } from '@/shared/types/response';

export type PartyPageProps = {
  party: {
    id: Pokemon['id'];
    name: Pokemon['name'];
    frontSprite: Pokemon['frontSprite'];
    level: Pokemon['level'];
  }[];
};
