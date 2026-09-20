import type { ElementalType } from './php-types';

export * from './auth';
export * from './php-types';

export type PaginatedList<T> = {
  currentPage: number;
  firstPageUrl: string;
  from: number;
  lastPage: number;
  lastPageUrl: string;
  links: {
    url: string;
    label: string;
    page: number | null;
    active: boolean;
  }[];
  nextPageUrl: string;
  path: number;
  perPage: number;
  prevPageUrl: string;
  to: number;
  total: number;
  data: T[];
};

export interface FilterOption<T extends string | number = string | number> {
  label: string;
  value: T;
}

export type SpeciesTypes = [ElementalType] | [ElementalType, ElementalType];

export type SpeciesSprites = {
  backDefault: string | null;
  backFemale: string | null;
  backShiny: string | null;
  backShinyFemale: string | null;
  frontDefault: string | null;
  frontFemale: string | null;
  frontShiny: string | null;
  frontShinyFemale: string | null;
};

export type Primitive = string | number | boolean | null | undefined;

export type Paths<T> = T extends Primitive
  ? never
  : {
      [K in keyof T & string]: T[K] extends Primitive
        ? K
        : K | `${K}.${Paths<T[K]>}`;
    }[keyof T & string];
