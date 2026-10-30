<template>
  <section :class="cn('@container/section flex flex-col gap-4', props.class)">
    <slot />
  </section>
</template>

<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/shared/lib/utils';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();
</script>
