import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

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
