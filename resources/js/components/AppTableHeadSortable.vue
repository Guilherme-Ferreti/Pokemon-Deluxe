<template>
  <TableHead>
    <Link
      class="flex items-center gap-2 [&_svg]:size-4"
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
import { removeFalsyValues, toQueryString } from '@/lib/utils';
import { SortDirection } from '@/types';
import TableHead from './ui/table/TableHead.vue';

interface AppTableColumnSortLinkProps {
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
}

const props = withDefaults(defineProps<AppTableColumnSortLinkProps>(), {
  sortAscIcon: IconSortAscending,
  sortDescIcon: IconSortDescending,
});

const visibleIcon = computed(() => {
  if (props.searchForm.sort !== props.sortBy) {
    return IconArrowsSort;
  }

  return props.searchForm.dir === SortDirection.ASC
    ? props.sortAscIcon
    : props.sortDescIcon;
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
