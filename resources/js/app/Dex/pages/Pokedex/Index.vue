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
import type { DexPokedexIndexPageProps } from '@/app/Dex/types/PokedexIndexPageProps';
import Pokedex from '@/app/Pokemon/components/Pokedex.vue';
import AppIconInBox from '@/shared/components/AppIconInBox.vue';
import AppIconNotInBox from '@/shared/components/AppIconNotInBox.vue';
import AppPageHeader from '@/shared/components/AppPageHeader.vue';
import AppSection from '@/shared/components/AppSection.vue';
import { route } from '@/shared/types/helpers/route';

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
