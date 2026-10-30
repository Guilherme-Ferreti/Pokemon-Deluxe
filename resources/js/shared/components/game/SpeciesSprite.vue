<template>
  <img
    class="size-24 shrink-0"
    loading="lazy"
    :src="url ?? undefined"
    :alt="name"
  />
</template>

<script setup lang="ts">
import type { Species } from '@/shared/types/response';

defineProps<{
  url: Species['sprites'][keyof Species['sprites']];
  name: Species['name'];
}>();
</script>
