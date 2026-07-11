<template>
  <div
    class="flex w-full flex-col items-center justify-center gap-2 @lg/section:flex-row @lg/section:justify-between"
  >
    <p class="text-sm text-muted-foreground">
      Showing {{ list.from }} to {{ list.to }} of {{ list.total }} results
    </p>
    <Pagination
      v-model:page="currentPage"
      :items-per-page="list.perPage"
      :total="list.total"
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
            :is-active="item.value === currentPage"
          >
            {{ item.value }}
          </PaginationItem>
        </template>
        <PaginationNext />
      </PaginationContent>
    </Pagination>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
  Pagination,
  PaginationContent,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/components/ui/pagination';
import type { PaginatedList } from '@/types';

const props = defineProps<{
  list: PaginatedList<unknown>;
  only?: Array<string>;
}>();

const currentPage = ref(props.list.currentPage);

watch(currentPage, () => {
  const url = props.list.links.find((link) => {
    return link.page === currentPage.value;
  })?.url;

  if (!url) {
    return;
  }

  router.get(
    url,
    {},
    { preserveScroll: true, preserveState: true, only: props.only },
  );
});
</script>
