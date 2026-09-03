<template>
  <img
    loading="lazy"
    :class="cn('size-8 rounded-sm', props.class)"
    :src="url"
    :alt="`${name}'s portrait`"
  />
</template>

<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  url?: string;
  name: string;
  class?: HTMLAttributes['class'];
}>();
</script>
