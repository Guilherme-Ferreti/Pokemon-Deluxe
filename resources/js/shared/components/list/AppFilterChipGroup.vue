<template>
  <div
    class="flex flex-wrap items-center gap-1.5"
    role="group"
    :aria-label="label"
  >
    <span
      v-if="visibleLabel"
      class="text-xs text-muted-foreground"
    >
      {{ visibleLabel }}
    </span>
    <Badge
      v-if="allLabel"
      as="button"
      type="button"
      class="cursor-pointer px-2.5 py-1"
      :variant="model === null ? 'default' : 'outline'"
      :aria-pressed="model === null"
      :data-test="testIdPrefix && `${testIdPrefix}-all-button`"
      @click="model = null"
    >
      {{ allLabel }}
    </Badge>
    <Badge
      v-for="option in options"
      :key="option.value"
      as="button"
      type="button"
      class="cursor-pointer px-2.5 py-1"
      :variant="model === option.value ? 'default' : 'outline'"
      :aria-pressed="model === option.value"
      :data-test="testIdPrefix && `${testIdPrefix}-${option.value}-button`"
      @click="model = option.value"
    >
      {{ option.label }}
    </Badge>
  </div>
</template>

<script setup lang="ts" generic="T extends string">
import { Badge } from '@/shared/components/ui/badge';
import type { FilterOption } from '@/shared/types';

defineProps<{
  options: FilterOption<T>[];
  label: string;
  allLabel?: string;
  visibleLabel?: string;
  testIdPrefix?: string;
}>();

const model = defineModel<T | null>({ required: true });
</script>
