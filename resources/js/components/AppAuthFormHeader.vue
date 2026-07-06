<template>
  <div class="flex flex-col items-center gap-1 text-center">
    <h1 class="text-2xl font-bold">{{ title }}</h1>
    <p
      v-if="description"
      class="text-sm text-balance text-muted-foreground"
    >
      {{ description }}
    </p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
  description?: string;
}>();
</script>
