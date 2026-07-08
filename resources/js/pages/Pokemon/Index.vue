<template>
  <p
    v-for="pokemon in pokemonInParty"
    :key="pokemon.id"
  >
    {{ pokemon.name }}
  </p>
</template>

<script setup lang="ts">
defineProps<{
  pokemonInParty: {
    id: string;
    name: string;
    frontSprite?: string;
    level: number;
  }[];
}>();
</script>
