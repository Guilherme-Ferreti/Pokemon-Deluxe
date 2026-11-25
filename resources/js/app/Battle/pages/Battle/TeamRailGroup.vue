<template>
  <section
    class="flex flex-col gap-1.5"
    :aria-labelledby="headingId"
  >
    <h3
      :id="headingId"
      class="text-xxs font-bold tracking-wider text-muted-foreground uppercase"
    >
      {{ heading }}
    </h3>
    <ul
      v-if="isList"
      class="grid grid-cols-1 gap-1.5 @md:grid-cols-2 @2xl:grid-cols-3 @5xl:flex @5xl:flex-col"
    >
      <slot />
    </ul>
    <slot v-else />
  </section>
</template>

<script setup lang="ts">
import { useId } from 'vue';

withDefaults(
  defineProps<{
    heading: string;
    isList?: boolean;
  }>(),
  {
    isList: true,
  },
);

const headingId = useId();
</script>
