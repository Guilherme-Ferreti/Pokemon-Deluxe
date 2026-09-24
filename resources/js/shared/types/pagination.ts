export type PaginatedList<T> = {
  currentPage: number;
  firstPageUrl: string;
  from: number | null;
  lastPage: number;
  lastPageUrl: string;
  links: {
    url: string | null;
    label: string;
    page: number | null;
    active: boolean;
  }[];
  nextPageUrl: string | null;
  path: string;
  perPage: number;
  prevPageUrl: string | null;
  to: number | null;
  total: number;
  data: T[];
};
