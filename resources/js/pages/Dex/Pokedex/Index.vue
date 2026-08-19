<template>
  <Head title="Pokedex" />
  <AppPageHeader
    title="Pokedex"
    description="Below is a list of all Pokemon in the game."
  />
  <p>
    The <AppIconInBox /> icon indicates pokemon you have in your box.
    <br />
    The <AppIconNotInBox /> icon indicates pokemon you do not have in your box.
  </p>
  <AppSection>
    <Pokedex
      :species-list="speciesList"
      :active-filters="activeFilters"
      :filter-options="filterOptions"
    />
  </AppSection>
</template>

<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';
import AppIconInBox from '@/components/AppIconInBox.vue';
import AppIconNotInBox from '@/components/AppIconNotInBox.vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSection from '@/components/AppSection.vue';
import Pokedex from '@/components/Pokedex.vue';
import type {
  ElementalType,
  FilterOption,
  Generation,
  PaginatedList,
  SearchPokedexSortBy,
  SortDirection,
  SpeciesTrait,
} from '@/types';

export interface DexPokedexIndexPageProps {
  speciesList: PaginatedList<{
    id: string;
    portrait: string;
    name: string;
    frontSprite: string;
    pokedexNumber: number;
    types: [ElementalType] | [ElementalType, ElementalType];
    generation: string;
    regularInBox: boolean;
    shinyInBox: boolean;
  }>;
  activeFilters: {
    search: string;
    types: ElementalType[];
    traits: SpeciesTrait[];
    generations: Generation[];
    sort: SearchPokedexSortBy;
    dir: SortDirection;
  };
  filterOptions: {
    types: FilterOption<ElementalType>[];
    traits: FilterOption<SpeciesTrait>[];
    generations: FilterOption<Generation>[];
  };
}

defineOptions({
  layout: () => ({ pageSize: 'small' }),
});

defineProps<DexPokedexIndexPageProps>();
</script>
