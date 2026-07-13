<template>
  <PokemonBoxSearch />
  <template v-if="pokemonList.data.length > 0">
    <div class="grid grid-cols-[repeat(auto-fill,minmax(16.25rem,1fr))] gap-4">
      <PokemonBoxCard
        v-for="pokemon in pokemonList.data"
        :key="pokemon.id"
        :pokemon="pokemon"
      />
    </div>
    <AppPagination
      :list="pokemonList"
      :only="['pokemonInBox']"
    />
  </template>
  <AppEmptyState
    v-else
    title="No Pokémon in your box"
    description="Catch some Pokémon to see them here."
    :icon="IconPokeball"
  />
</template>

<script setup lang="ts">
import { IconPokeball } from '@tabler/icons-vue';
import type { PaginatedList } from '@/types/index.js';
import type { ElementalType } from '@/types/pokemon.js';
import AppEmptyState from './AppEmptyState.vue';
import AppPagination from './AppPagination.vue';
import PokemonBoxCard from './PokemonBoxCard.vue';
import PokemonBoxSearch from './PokemonBoxSearch.vue';

defineProps<{
  pokemonList: PaginatedList<{
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
  }>;
}>();
</script>
