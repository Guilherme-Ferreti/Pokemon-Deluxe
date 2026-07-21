<template>
  <Head title="Your Pokemon" />
  <AppSection aria-labelledby="your-pokemon__heading">
    <AppSectionHeading
      id="your-pokemon__heading"
      title="Your Pokemon"
      description="Manage your party."
      :icon="IconPokeball"
    />
    <PokemonParty :pokemon-list="pokemonInParty" />
  </AppSection>
  <AppSection aria-labelledby="box__heading">
    <AppSectionHeading
      id="box__heading"
      title="Box"
      description="Manage your captured Pokemon."
      :icon="IconArchive"
    />
    <PokemonBox
      :pokemon-list="pokemonBox"
      :search-filters="searchFilters"
      :filter-options="filterOptions"
    />
  </AppSection>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { IconArchive, IconPokeball } from '@tabler/icons-vue';
import AppSection from '@/components/AppSection.vue';
import AppSectionHeading from '@/components/AppSectionHeading.vue';
import PokemonBox from '@/components/PokemonBox.vue';
import PokemonParty from '@/components/PokemonParty.vue';
import type {
  ElementalType,
  FilterOption,
  Generation,
  PaginatedList,
  PokemonTrait,
  SearchBoxSortBy,
  SortDirection,
} from '@/types';

export interface PokemonIndexPageProps {
  pokemonInParty: {
    id: string;
    name: string;
    frontSprite?: string;
    level: number;
  }[];
  pokemonBox: PaginatedList<{
    id: string;
    name: string;
    frontSprite?: string;
    level: number;
    experience: number;
    pokedex: {
      types: [ElementalType] | [ElementalType, ElementalType];
    };
    moves: {
      name: string;
    }[];
  }>;
  searchFilters: {
    search: string;
    types: ElementalType[];
    traits: PokemonTrait[];
    generations: Generation[];
    sort: SearchBoxSortBy;
    dir: SortDirection;
  };
  filterOptions: {
    types: FilterOption<ElementalType>[];
    traits: FilterOption<PokemonTrait>[];
    generations: FilterOption<Generation>[];
    sort: FilterOption<SearchBoxSortBy>[];
    dir: FilterOption<SortDirection>[];
  };
}

defineProps<PokemonIndexPageProps>();
</script>
