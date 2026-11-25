import { router, usePage } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import { ref, watch } from 'vue';
import { getBasePath } from '@/shared/lib/utils';

export function useTabInUrl<T extends string>(
  tabs: readonly T[],
  defaultTab: T,
): Ref<T> {
  const page = usePage();

  const isTab = (value: string | null): value is T => tabs.includes(value as T);

  const tabInUrl = new URLSearchParams(page.url.split('?')[1] ?? '').get('tab');

  const selectedTab = ref(isTab(tabInUrl) ? tabInUrl : defaultTab) as Ref<T>;

  watch(selectedTab, (tab) => {
    router.replace({
      url: `${getBasePath(page.url)}?tab=${tab}`,
      preserveScroll: true,
      preserveState: true,
    });
  });

  return selectedTab;
}
