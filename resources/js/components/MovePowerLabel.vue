<template>
  <template v-if="category === MoveCategory.OHKO">One-hit KO</template>
  <template v-else>{{ power }}</template>
</template>

<script setup lang="ts">
import { MoveCategory } from '@/types';

defineProps<{
  category: MoveCategory;
  power?: number;
}>();
</script>
