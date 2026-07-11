<template>
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
