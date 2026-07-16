<template>
  <div class="relative">
    <slot />
    <Badge
      v-if="show && text"
      variant="destructive"
      class="absolute top-0 right-0 size-4 translate-x-1/2 -translate-y-1/2 transform rounded-full px-1 font-mono font-semibold tabular-nums"
    >
      {{ text }}
    </Badge>
    <div
      v-if="show && !text"
      class="absolute top-0 right-0 size-2 translate-x-1/2 -translate-y-1/2 transform rounded-full bg-destructive"
    ></div>
  </div>
</template>
<script setup lang="ts">
import { Badge } from './ui/badge';

defineProps<{
  show: boolean;
  text?: string;
}>();
</script>
