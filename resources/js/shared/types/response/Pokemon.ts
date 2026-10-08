import type { PokemonGender } from '@/shared/types/enums/PokemonGender';
import type { Species } from '@/shared/types/response/Species';

export type Pokemon = {
  id: string;
  userId: string;
  originalUserId: string;
  speciesId: string;
  level: number;
  experience: number;
  inParty: boolean;
  isShiny: boolean;
  gender: PokemonGender;
  pokeballId: string;
  capturedAt: string;
  createdAt: string;
  updatedAt: string;
  frontSprite:
    Species['sprites']['frontDefault'] | Species['sprites']['frontShiny'];
  name: string;
};
