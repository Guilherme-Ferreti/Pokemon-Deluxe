<template>
  <span>
    {{ name }}
    <sup
      v-if="isZMove || isDynamaxMove"
      class="font-extrabold"
      aria-hidden="true"
    >
      {{ isZMove ? 'Z' : 'D' }}
    </sup>
  </span>
  <span class="sr-only">{{ isZMove ? '(Z Move)' : '(Dynamax Move)' }}</span>
</template>

<script setup lang="ts">
import type { Move } from '@/shared/types/response';

defineProps<{
  name: Move['name'];
  isZMove: Move['isZMove'];
  isDynamaxMove: Move['isDynamaxMove'];
}>();
</script>
