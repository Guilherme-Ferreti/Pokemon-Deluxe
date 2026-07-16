<template>
  <PokemonBoxSearchBar
    :search-filters="searchFilters"
    :filter-options="filterOptions"
  />
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
      :only="['pokemonBox']"
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
import type { PokemonIndexPageProps } from '@/pages/Pokemon/Index.vue';
import AppEmptyState from './AppEmptyState.vue';
import AppPagination from './AppPagination.vue';
import PokemonBoxCard from './PokemonBoxCard.vue';
import PokemonBoxSearchBar from './PokemonBoxSearchBar.vue';

defineProps<{
  pokemonList: PokemonIndexPageProps['pokemonBox'];
  searchFilters: PokemonIndexPageProps['searchFilters'];
  filterOptions: PokemonIndexPageProps['filterOptions'];
}>();
</script>
