<template>
  <img
    :class="cn('inline-block size-7', props.class)"
    :alt="`${name} sprite`"
    :src="url"
  />
</template>

<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/shared/lib/utils';

const props = defineProps<{
  name: string;
  url: string;
  class?: HTMLAttributes['class'];
}>();
</script>
