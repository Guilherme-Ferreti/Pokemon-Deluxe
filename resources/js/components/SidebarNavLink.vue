<template>
  <Link
    :href="url"
    :aria-current="isCurrentPage(url) ? 'page' : undefined"
  >
    <component
      :is="icon"
      v-if="icon"
    />
    <span>{{ title }}</span>
  </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Icon } from '@tabler/icons-vue';
import { isCurrentPage } from '@/lib/utils';

defineProps<{
  title: string;
  url: string;
  icon?: Icon;
}>();
</script>
