<template>
  <Progress
    :class="['hp-progress', `hp-progress--${level}`]"
    :model-value="percentage"
    :aria-label="label"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Progress } from '@/shared/components/ui/progress';

const props = defineProps<{
  percentage: number;
  label: string;
}>();

const level = computed(() => {
  if (props.percentage > 50) {
    return 'good';
  }

  return props.percentage >= 20 ? 'warning' : 'danger';
});
</script>

<style scoped>
@reference '@css/app.css';

.hp-progress {
  @apply min-w-0 flex-1;
}

.hp-progress :deep([data-slot='progress-indicator']) {
  @apply duration-500 ease-out motion-reduce:transition-none;
}

.hp-progress--good :deep([data-slot='progress-indicator']) {
  @apply bg-green-600 dark:bg-green-500;
}

.hp-progress--warning :deep([data-slot='progress-indicator']) {
  @apply bg-yellow-500 dark:bg-yellow-400;
}

.hp-progress--danger :deep([data-slot='progress-indicator']) {
  @apply bg-destructive;
}
</style>
