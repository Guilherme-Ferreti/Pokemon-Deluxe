<template>
  <div class="flex flex-col gap-0.5">
    <h1
      class="text-2xl font-bold text-foreground"
      data-test="page-header__title"
    >
      {{ title }}
    </h1>
    <p class="text-sm text-muted-foreground">
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
