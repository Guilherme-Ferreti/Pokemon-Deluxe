import type { PokemonGender } from '@/shared/types/enums/PokemonGender';
import type { Species } from '@/shared/types/response/Species';

export type Pokemon = {
  id: string;
  name: string;
  level: number;
  experience: number;
  gender: PokemonGender;
  frontSprite:
    Species['sprites']['frontDefault'] | Species['sprites']['frontShiny'];
};
