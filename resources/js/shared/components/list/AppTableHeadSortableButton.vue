<template>
  <TableHead
    class="px-0"
    :aria-sort="ariaSort"
    :data-test="`header-${sortBy}`"
  >
    <button
      class="inline-flex size-full shrink-0 items-center gap-2 px-2 -outline-offset-1 [&_svg]:size-4"
      type="button"
      :aria-label="`Sort by ${columnName.toLowerCase()}`"
    >
      <span>{{ columnName }}</span>
      <component :is="visibleIcon" />
    </button>
  </TableHead>
</template>

<script setup lang="ts">
import type { Icon } from '@tabler/icons-vue';
import {
  IconArrowsSort,
  IconSortAscending,
  IconSortDescending,
} from '@tabler/icons-vue';
import { computed } from 'vue';
import { TableHead } from '@/shared/components/ui/table';
import { SortDirection } from '@/shared/types/enums';

const props = defineProps<{
  columnName: string;
  sortBy: string;
  currentSort: string;
  currentDir: SortDirection;
  sortAscIcon?: Icon;
  sortDescIcon?: Icon;
}>();

const visibleIcon = computed(() => {
  if (props.currentSort !== props.sortBy) {
    return IconArrowsSort;
  }

  return props.currentDir === SortDirection.ASC
    ? props.sortAscIcon || IconSortAscending
    : props.sortDescIcon || IconSortDescending;
});

const ariaSort = computed(() => {
  if (props.currentSort !== props.sortBy) {
    return 'none';
  }

  return props.currentDir === SortDirection.ASC ? 'ascending' : 'descending';
});
</script>
