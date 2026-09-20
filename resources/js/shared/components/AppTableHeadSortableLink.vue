<template>
  <TableHead class="px-0">
    <Link
      class="inline-flex size-full shrink-0 items-center gap-2 px-2 -outline-offset-1 [&_svg]:size-4"
      aria-current="page"
      :aria-label="`Sort by ${columnName.toLowerCase()}`"
      :href="sortByUrl"
    >
      <span>{{ columnName }}</span>
      <component :is="visibleIcon" />
    </Link>
  </TableHead>
</template>

<script setup lang="ts" generic="T extends string">
import type { InertiaForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import type { Icon } from '@tabler/icons-vue';
import {
  IconArrowsSort,
  IconSortAscending,
  IconSortDescending,
} from '@tabler/icons-vue';
import { computed } from 'vue';
import TableHead from '@/shared/components/ui/table/TableHead.vue';
import { removeFalsyValues, toQueryString } from '@/shared/lib/utils';
import { SortDirection } from '@/shared/types';

const props = defineProps<{
  columnName: string;
  sortBy: T;
  defaultSortBy: T;
  searchForm: InertiaForm<{
    sort: T;
    dir: SortDirection;
  }>;
  routeUrl: string;
  sortAscIcon?: Icon;
  sortDescIcon?: Icon;
}>();

const visibleIcon = computed(() => {
  if (props.searchForm.sort !== props.sortBy) {
    return IconArrowsSort;
  }

  return props.searchForm.dir === SortDirection.ASC
    ? props.sortAscIcon || IconSortAscending
    : props.sortDescIcon || IconSortDescending;
});

const sortByUrl = computed(function () {
  const data = props.searchForm.data();

  if (data.sort !== props.sortBy) {
    data.sort = props.sortBy;
    data.dir = SortDirection.ASC;
  } else if (data.dir === SortDirection.ASC) {
    data.dir = SortDirection.DESC;
  } else if (data.dir === SortDirection.DESC) {
    data.sort = props.defaultSortBy;
    data.dir = SortDirection.ASC;
  }

  return `${props.routeUrl}?${toQueryString(removeFalsyValues(data)).toString()}`;
});
</script>
