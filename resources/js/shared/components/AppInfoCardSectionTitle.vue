<template>
  <span
    v-if="!icon"
    class="text-sm font-bold"
  >
    {{ title }}
  </span>
  <span
    v-else
    class="flex items-center gap-2 text-sm font-bold"
  >
    <span>{{ title }}</span>
    <Tooltip v-if="iconTooltipText">
      <TooltipTrigger as-child>
        <component
          :is="icon"
          size="14"
        />
      </TooltipTrigger>
      <TooltipContent>Global numbers are updated once per day</TooltipContent>
    </Tooltip>
    <component
      :is="icon"
      v-else
      size="14"
    />
  </span>
</template>

<script setup lang="ts">
import type { Icon } from '@tabler/icons-vue';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';

defineProps<{
  title: string;
  icon?: Icon;
  iconTooltipText?: string;
}>();
</script>
