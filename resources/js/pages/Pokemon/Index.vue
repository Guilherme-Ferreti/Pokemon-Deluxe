<template>
  <PartyCard :pokemon-in-party="pokemonInParty" />
</template>

<script setup lang="ts">
import PartyCard from '@/components/PartyCard.vue';

defineProps<{
  pokemonInParty: {
    id: string;
    name: string;
    frontSprite?: string;
    level: number;
  }[];
}>();
</script>
