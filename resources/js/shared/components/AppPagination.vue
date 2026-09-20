<template>
  <div
    class="flex w-full flex-col items-center justify-center gap-2 @xl/section:flex-row @xl/section:justify-between"
  >
    <p class="text-sm text-muted-foreground">
      Showing {{ list.from }} to {{ list.to }} of {{ list.total }} results
    </p>
    <Pagination
      :page="list.currentPage"
      :items-per-page="list.perPage"
      :total="list.total"
      :show-edges="true"
      :sibling-count="1"
      @update:page="(value) => navigateToPage(value)"
    >
      <PaginationContent v-slot="{ items }">
        <PaginationPrevious />
        <template
          v-for="(item, index) in items"
          :key="index"
        >
          <PaginationItem
            v-if="item.type === 'page'"
            :value="item.value"
            :is-active="item.value === list.currentPage"
          >
            {{ item.value }}
          </PaginationItem>
          <PaginationEllipsis v-else />
        </template>
        <PaginationNext />
      </PaginationContent>
    </Pagination>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/shared/components/ui/pagination';
import type { PaginatedList } from '@/shared/types';

const props = defineProps<{
  list: PaginatedList<unknown>;
  only?: Array<string>;
  preserveScroll?: boolean;
}>();

function navigateToPage(page: number) {
  const url = props.list.links.find((link) => {
    return link.page === page;
  })?.url;

  if (!url) {
    return;
  }

  router.get(
    url,
    {},
    {
      preserveScroll: props.preserveScroll,
      preserveState: true,
      only: props.only,
    },
  );
}
</script>
