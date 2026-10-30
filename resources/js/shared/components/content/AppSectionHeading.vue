<template>
  <div class="flex flex-col gap-0.5">
    <h2
      :id="id"
      class="flex items-center gap-1 text-xl font-bold text-foreground"
    >
      <component
        :is="icon"
        v-if="icon"
      />
      <span>{{ title }}</span>
    </h2>
    <p
      :class="{
        'text-sm text-muted-foreground': true,
        'ml-7': icon,
      }"
    >
      {{ description }}
    </p>
  </div>
</template>

<script setup lang="ts">
import type { Icon } from '@tabler/icons-vue';

defineProps<{
  title: string;
  description?: string;
  id?: string;
  icon?: Icon;
}>();
</script>
