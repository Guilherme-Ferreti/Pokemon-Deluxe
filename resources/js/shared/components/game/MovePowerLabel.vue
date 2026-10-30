<template>
  <template v-if="category === MoveCategory.OHKO">One-hit KO</template>
  <template v-else>{{ power }}</template>
</template>

<script setup lang="ts">
import { MoveCategory } from '@/shared/types/enums';
import type { Move } from '@/shared/types/response';

defineProps<{
  category: Move['category'];
  power: Move['power'];
}>();
</script>
