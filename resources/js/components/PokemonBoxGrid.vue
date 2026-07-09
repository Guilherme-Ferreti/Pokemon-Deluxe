<template>
  <div>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-4">
      <PokemonBoxCard
        v-for="pokemon in pokemonList"
        :key="pokemon.id"
        :pokemon="pokemon"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ElementalType } from '@/types/pokemon.js';
import PokemonBoxCard from './PokemonBoxCard.vue';

defineProps<{
  pokemonList: {
    id: string;
    name: string;
    frontSprite?: string;
    level: number;
    experience: number;
    pokedex: {
      primaryType: ElementalType;
      secondaryType: ElementalType | null;
    };
    moves: {
      name: string;
    }[];
  }[];
}>();
</script>
