export type Primitive = string | number | boolean | null | undefined;

export type Paths<T> = T extends Primitive
  ? never
  : {
      [K in keyof T & string]: T[K] extends Primitive
        ? K
        : K | `${K}.${Paths<T[K]>}`;
    }[keyof T & string];
