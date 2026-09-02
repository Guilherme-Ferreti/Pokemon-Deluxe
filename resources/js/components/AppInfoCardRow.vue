<template>
  <div class="app-info-card-row">
    <span class="app-info-card-row__title">{{ title }}</span>
    <span
      v-if="text"
      class="app-info-card-row__text"
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

<style lang="css" scoped>
@reference '../../css/app.css';

.app-info-card-row {
  @apply flex flex-wrap items-center justify-between gap-3;
}

.app-info-card-row__title {
  @apply text-sm font-medium text-muted-foreground;
}

.app-info-card-row__text {
  @apply text-sm text-foreground;
}
</style>
