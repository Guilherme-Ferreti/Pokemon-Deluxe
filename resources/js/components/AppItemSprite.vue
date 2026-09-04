<template>
  <img
    class="inline-block size-7"
    :alt="`${name} sprite`"
    :src="url"
  />
</template>

<script setup lang="ts">
defineProps<{
  name: string;
  url: string;
}>();
</script>
