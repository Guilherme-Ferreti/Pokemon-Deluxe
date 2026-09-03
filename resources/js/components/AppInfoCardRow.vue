<template>
  <div class="flex flex-wrap items-center justify-between gap-3">
    <span class="text-sm font-medium text-muted-foreground">{{ title }}</span>
    <span
      v-if="text"
      class="text-sm text-foreground"
    >
      {{ text }}
    </span>
    <template v-else>
      <slot />
    </template>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
  text?: string;
}>();
</script>
