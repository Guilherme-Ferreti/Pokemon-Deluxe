<template>
  <template v-if="category === MoveCategory.OHKO">One-hit KO</template>
  <template v-else>{{ power }}</template>
</template>

<script setup lang="ts">
import type { Move } from '@/shared/types';
import { MoveCategory } from '@/shared/types';

defineProps<{
  category: Move['category'];
  power: Move['power'];
}>();
</script>
