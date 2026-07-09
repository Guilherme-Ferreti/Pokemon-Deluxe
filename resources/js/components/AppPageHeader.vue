<template>
  <div>
    <h1 class="text-2xl font-bold text-foreground">{{ title }}</h1>
    <p class="mt-0.5 text-sm text-muted-foreground">
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
