<template>
  <Progress
    :class="[
      'min-w-0 flex-1 *:data-[slot=progress-indicator]:duration-1000 *:data-[slot=progress-indicator]:ease-out motion-reduce:*:data-[slot=progress-indicator]:transition-none',
      indicatorColors[level],
    ]"
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

const indicatorColors = {
  good: '*:data-[slot=progress-indicator]:bg-green-600 dark:*:data-[slot=progress-indicator]:bg-green-500',
  warning:
    '*:data-[slot=progress-indicator]:bg-yellow-500 dark:*:data-[slot=progress-indicator]:bg-yellow-400',
  danger: '*:data-[slot=progress-indicator]:bg-destructive',
};

const level = computed<keyof typeof indicatorColors>(() => {
  if (props.percentage > 50) {
    return 'good';
  }

  return props.percentage >= 20 ? 'warning' : 'danger';
});
</script>
