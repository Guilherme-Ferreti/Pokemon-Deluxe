<template>
  <Head title="Pokédex" />
  <AppPageHeader
    title="Pokédex"
    description="Below is a list of all Pokémon in the game."
  />
  <p>
    The <AppIconInBox /> icon indicates pokémon you have in your box.
    <br />
    The <AppIconNotInBox /> icon indicates pokémon you do not have in your box.
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
import { Head, setLayoutProps } from '@inertiajs/vue3';
import AppIconInBox from '@/components/AppIconInBox.vue';
import AppIconNotInBox from '@/components/AppIconNotInBox.vue';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSection from '@/components/AppSection.vue';
import Pokedex from '@/components/Pokedex.vue';
import type { DexPokedexIndexPageProps } from '@/types';
import { route } from '@/types/helpers/route';

defineProps<DexPokedexIndexPageProps>();

setLayoutProps({
  pageSize: 'small',
  breadcrumbs: [
    {
      name: 'Pokédex',
      href: route('dex.web.pokedex.index'),
    },
    {
      name: 'List',
    },
  ],
});
</script>
