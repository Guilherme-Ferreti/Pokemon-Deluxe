<template>
  <img
    loading="lazy"
    :class="cn('size-24 shrink-0', props.class)"
    :src="url ?? undefined"
    :alt="name"
  />
</template>

<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/shared/lib/utils';
import type { Species } from '@/shared/types/response';

const props = defineProps<{
  url: Species['sprites'][keyof Species['sprites']];
  name: Species['name'];
  class?: HTMLAttributes['class'];
}>();
</script>
