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
import type { Species } from '@/shared/types/models';

const props = defineProps<{
  url: Species['portraits'][keyof Species['portraits']];
  name: Species['name'];
  class?: HTMLAttributes['class'];
}>();
</script>
