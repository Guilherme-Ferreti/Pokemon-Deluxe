<template>
  <Head :title="species.name" />

  <p>{{ species.name }}</p>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
  species: {
    id: string;
    name: string;
  };
}>();
</script>
