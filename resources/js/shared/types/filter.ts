export interface FilterOption<T extends string | number = string | number> {
  label: string;
  value: T;
}
