import { ref, readonly } from 'vue';

export interface BreadcrumbItem {
  title: string;
  href?: string;
}

const breadcrumbs = ref<BreadcrumbItem[]>([]);

export function useBreadcrumbs() {
  return {
    breadcrumbs: readonly(breadcrumbs),
    setBreadcrumbs: (items: BreadcrumbItem[]) => {
      breadcrumbs.value = items;
    },
  };
}
