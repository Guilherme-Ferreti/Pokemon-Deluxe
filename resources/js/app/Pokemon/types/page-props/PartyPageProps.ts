import type { Pokemon, Species } from '@/shared/types/response';

export type PartyMemberSlot = {
  id: Pokemon['id'];
  name: Pokemon['name'];
  gender: Pokemon['gender'];
  portrait: string | null;
  level: Pokemon['level'];
  boost: number;
  types: Species['types'];
};

export type PartyPageProps = {
  party: PartyMemberSlot[];
  isBattleOngoing: boolean;
  maxPartySize: number;
};
