<template>
  <span>
    {{ name }}
    <sup
      v-if="isZMove || isDynamax"
      class="font-extrabold"
      aria-hidden="true"
    >
      {{ isZMove ? 'Z' : 'D' }}
    </sup>
  </span>
  <span class="sr-only">{{ isZMove ? '(Z Move)' : '(Dynamax Move)' }}</span>
</template>

<script setup lang="ts">
defineProps<{
  name: string;
  isZMove: boolean;
  isDynamax: boolean;
}>();
</script>
