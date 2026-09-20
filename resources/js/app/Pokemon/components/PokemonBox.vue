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
    data-test="pokemon-box__empty-state"
    :icon="IconPokeball"
  />
</template>

<script setup lang="ts">
import { IconPokeball } from '@tabler/icons-vue';
import PokemonBoxCard from '@/app/Pokemon/components/PokemonBoxCard.vue';
import PokemonBoxSearchBar from '@/app/Pokemon/components/PokemonBoxSearchBar.vue';
import type { PokemonBoxIndexPageProps } from '@/app/Pokemon/types/BoxIndexPageProps';
import AppEmptyState from '@/shared/components/AppEmptyState.vue';
import AppPagination from '@/shared/components/AppPagination.vue';

defineProps<{
  pokemonList: PokemonBoxIndexPageProps['pokemonList'];
  activeFilters: PokemonBoxIndexPageProps['activeFilters'];
  filterOptions: PokemonBoxIndexPageProps['filterOptions'];
}>();
</script>
