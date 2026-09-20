<template>
  <Empty>
    <EmptyHeader>
      <EmptyMedia
        v-if="icon"
        variant="icon"
      >
        <component :is="icon" />
      </EmptyMedia>
      <EmptyTitle>{{ title }}</EmptyTitle>
      <EmptyDescription>{{ description }}</EmptyDescription>
    </EmptyHeader>
    <EmptyContent v-if="$slots.default">
      <slot />
    </EmptyContent>
  </Empty>
</template>

<script setup lang="ts">
import type { Icon } from '@tabler/icons-vue';
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from '@/shared/components/ui/empty';

defineProps<{
  title: string;
  description: string;
  icon?: Icon;
}>();
</script>
