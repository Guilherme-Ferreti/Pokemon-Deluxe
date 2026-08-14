<template>
  <Head title="Your Box" />
  <AppPageHeader
    title="Your Box"
    description="Manage your captured Pokemon."
  />
  <AppSection>
    <PokemonBox
      :pokemon-list="pokemonList"
      :active-filters="activeFilters"
      :filter-options="filterOptions"
    />
  </AppSection>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSection from '@/components/AppSection.vue';
import PokemonBox from '@/components/PokemonBox.vue';
import type {
  ElementalType,
  FilterOption,
  Generation,
  PaginatedList,
  PokemonTrait,
  SearchBoxSortBy,
  SortDirection,
} from '@/types';

export interface PokemonBoxIndexPageProps {
  pokemonList: PaginatedList<{
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
  activeFilters: {
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

defineProps<PokemonBoxIndexPageProps>();
</script>
