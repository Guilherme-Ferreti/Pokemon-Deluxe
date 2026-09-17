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
    page?: number;
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
  backDefault?: string;
  backFemale?: string;
  backShiny?: string;
  backShinyFemale?: string;
  frontDefault?: string;
  frontFemale?: string;
  frontShiny?: string;
  frontShinyFemale?: string;
};

export type Primitive = string | number | boolean | null | undefined;

export type Paths<T> = T extends Primitive
  ? never
  : {
      [K in keyof T & string]: T[K] extends Primitive
        ? K
        : K | `${K}.${Paths<T[K]>}`;
    }[keyof T & string];
