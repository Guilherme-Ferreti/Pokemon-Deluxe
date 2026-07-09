<template>
  <div>
    <h2
      class="flex items-center gap-1 text-xl font-bold text-foreground"
      :id="id"
    >
      <component
        v-if="icon"
        :is="icon"
      />
      <span>{{ title }}</span>
    </h2>
    <p
      :class="{
        'mt-0.5 text-sm text-muted-foreground': true,
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
