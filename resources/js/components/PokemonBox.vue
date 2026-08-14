<template>
  <PokemonBoxSearchBar
    :active-filters="activeFilters"
    :filter-options="filterOptions"
  />
  <template v-if="pokemonList.data.length > 0">
    <ul class="grid grid-cols-[repeat(auto-fill,minmax(16.25rem,1fr))] gap-4">
      <PokemonBoxCard
        v-for="pokemon in pokemonList.data"
        :key="pokemon.id"
        :pokemon="pokemon"
      />
    </ul>
    <AppPagination
      :list="pokemonList"
      :only="['pokemonList']"
      preserve-scroll
    />
  </template>
  <AppEmptyState
    v-else
    title="No Pokémon in your box"
    description="Catch some Pokémon to see them here."
    data-test="pokemon-box-empty-state"
    :icon="IconPokeball"
  />
</template>

<script setup lang="ts">
import { IconPokeball } from '@tabler/icons-vue';
import type { PokemonBoxIndexPageProps } from '@/pages/Pokemon/Box/Index.vue';
import AppEmptyState from './AppEmptyState.vue';
import AppPagination from './AppPagination.vue';
import PokemonBoxCard from './PokemonBoxCard.vue';
import PokemonBoxSearchBar from './PokemonBoxSearchBar.vue';

defineProps<{
  pokemonList: PokemonBoxIndexPageProps['pokemonList'];
  activeFilters: PokemonBoxIndexPageProps['activeFilters'];
  filterOptions: PokemonBoxIndexPageProps['filterOptions'];
}>();
</script>
