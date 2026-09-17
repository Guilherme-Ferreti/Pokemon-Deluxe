import { computed, ref } from 'vue';
import { sortBy } from '@/lib/utils';
import type { Paths } from '@/types';
import { SortDirection } from '@/types';

export function useSortable<T extends Record<string, unknown>>(
  items: T[],
  defaultSort: Paths<T>,
  defaultDirection: SortDirection = SortDirection.ASC,
) {
  const sort = ref(defaultSort);
  const direction = ref(defaultDirection);

  const sortedItems = computed(() =>
    sortBy(items, sort.value, direction.value),
  );

  function toggleSort(sortValue: Paths<T>) {
    if (sort.value !== sortValue) {
      sort.value = sortValue;
      direction.value = SortDirection.ASC;
    } else if (direction.value === SortDirection.ASC) {
      direction.value = SortDirection.DESC;
    } else if (direction.value === SortDirection.DESC) {
      sort.value = defaultSort;
      direction.value = defaultDirection;
    }
  }

  return { sortedItems, sort, direction, toggleSort };
}
