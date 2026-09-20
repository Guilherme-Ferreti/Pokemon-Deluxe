<template>
  <div class="grid gap-4 sm:grid-cols-[1fr_3fr] sm:gap-4">
    <p class="text-sm font-semibold">{{ title }}</p>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
}>();
</script>
