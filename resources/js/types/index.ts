export * from './auth';
export * from './pokemon';

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
