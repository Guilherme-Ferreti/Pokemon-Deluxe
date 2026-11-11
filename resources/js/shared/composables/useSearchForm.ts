import { useForm } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { nextTick } from 'vue';
import { removeFalsyValues } from '@/shared/lib/utils';

export function useSearchForm<T extends { search: string | null }>(
  activeFilters: T,
  url: string,
  only: string[],
) {
  const searchForm = useForm({
    ...activeFilters,
  } as never) as unknown as InertiaForm<T>;

  let lastSubmittedSearch = activeFilters.search;

  watchDebounced(
    () => searchForm.search,
    (search) => {
      if (search !== lastSubmittedSearch) {
        submitSearch();
      }
    },
    { debounce: 450 },
  );

  function submitSearch() {
    lastSubmittedSearch = searchForm.search;

    searchForm.transform(removeFalsyValues).get(url, {
      preserveScroll: true,
      preserveState: true,
      only,
    });
  }

  function resetUnappliedChanges() {
    nextTick(() => !searchForm.processing && searchForm.reset());
  }

  return { searchForm, submitSearch, resetUnappliedChanges };
}
