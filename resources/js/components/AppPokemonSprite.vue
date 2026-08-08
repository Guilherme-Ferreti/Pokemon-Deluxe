<template>
  <img
    class="size-24 shrink-0"
    loading="lazy"
    :src="url"
    :alt="name"
  />
</template>

<script setup lang="ts">
defineProps<{
  url?: string;
  name: string;
}>();
</script>
