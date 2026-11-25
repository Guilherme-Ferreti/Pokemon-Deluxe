<template>
  <img
    v-if="url"
    :class="cn('inline-block size-8', props.class)"
    :alt="`${name} sprite`"
    :src="url"
  />
  <div
    v-else
    aria-hidden="true"
    :class="cn('inline-block size-8', props.class)"
  />
</template>

<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/shared/lib/utils';

const props = defineProps<{
  name: string;
  url?: string | null;
  class?: HTMLAttributes['class'];
}>();
</script>
