<template>
  <img
    loading="lazy"
    :class="cn('size-8 rounded-sm', props.class)"
    :src="url ?? undefined"
    :alt="`${name}'s portrait`"
  />
</template>

<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/shared/lib/utils';

const props = defineProps<{
  url: string | null;
  name: string;
  class?: HTMLAttributes['class'];
}>();
</script>
