<template>
  <img
    class="size-8 rounded-sm"
    loading="lazy"
    :src="url"
    :alt="`${name}'s portrait`"
  />
</template>

<script setup lang="ts">
defineProps<{
  url?: string;
  name: string;
}>();
</script>
