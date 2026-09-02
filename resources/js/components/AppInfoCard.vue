<template>
  <Card class="gap-4">
    <CardHeader>
      <CardTitle>{{ title }}</CardTitle>
    </CardHeader>
    <CardContent class="flex flex-col gap-4">
      <slot />
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
  title: string;
}>();
</script>
