import { usePage } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { Paths } from '@/shared/types';
import { SortDirection } from '@/shared/types';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function getBasePath(url: string): string {
  const hasProtocol = url.includes('://');
  const basePath = url.split('?')[0];

  if (!hasProtocol) {
    return basePath;
  }

  const { pathname } = new URL(url);

  return pathname;
}

export function isCurrentPage(url: string): boolean {
  return getBasePath(usePage().url) === getBasePath(url);
}

export function removeFalsyValues<T extends Record<string, unknown>>(
  obj: T,
): Partial<T> {
  return Object.fromEntries(
    Object.entries(obj).filter(([, value]) => value || false),
  ) as Partial<T>;
}

export function toQueryString(
  obj: Record<
    string,
    string | number | boolean | Array<string | number | boolean>
  >,
): string {
  const params = new URLSearchParams();

  for (const [key, value] of Object.entries(obj)) {
    if (Array.isArray(value)) {
      value.forEach((item, index) => {
        params.append(`${key}[${index}]`, String(item));
      });
    } else {
      params.append(key, String(value));
    }
  }

  return params.toString();
}

export function anyItemExists<T>(array: T[], items: T[]): boolean {
  return items.some((item) => array.includes(item));
}

export function formatPokedexNumber(pokedexNumber: number): string {
  return pokedexNumber.toString().padStart(4, '0');
}

export function sortBy<T extends object>(
  array: T[],
  key: Paths<T>,
  direction: SortDirection = SortDirection.ASC,
): T[] {
  const getValue = (obj: T, path: string): unknown =>
    path
      .split('.')
      .reduce(
        (value, key) => (value as Record<string, unknown>)?.[key],
        obj as unknown,
      );

  return [...array].sort((a, b) => {
    const aValue = getValue(a, key);
    const bValue = getValue(b, key);

    if (aValue === bValue) {
      return 0;
    }

    if (aValue == null) {
      return 1;
    }

    if (bValue == null) {
      return -1;
    }

    const result = aValue < bValue ? -1 : 1;

    return direction === SortDirection.ASC ? result : -result;
  });
}
