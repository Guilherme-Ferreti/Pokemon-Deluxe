<template>
  <PokemonBoxSearchBar
    :active-filters="activeFilters"
    :filter-options="filterOptions"
  />
  <template v-if="box.data.length > 0">
    <ul class="grid grid-cols-[repeat(auto-fill,minmax(16.25rem,1fr))] gap-4">
      <PokemonBoxCard
        v-for="pokemon in box.data"
        :key="pokemon.id"
        :pokemon="pokemon"
      />
    </ul>
    <AppPagination
      :list="box"
      :only="['box']"
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
import type { PokemonBoxPageProps } from '@/pages/Pokemon/Box.vue';
import AppEmptyState from './AppEmptyState.vue';
import AppPagination from './AppPagination.vue';
import PokemonBoxCard from './PokemonBoxCard.vue';
import PokemonBoxSearchBar from './PokemonBoxSearchBar.vue';

defineProps<{
  box: PokemonBoxPageProps['box'];
  activeFilters: PokemonBoxPageProps['activeFilters'];
  filterOptions: PokemonBoxPageProps['filterOptions'];
}>();
</script>
