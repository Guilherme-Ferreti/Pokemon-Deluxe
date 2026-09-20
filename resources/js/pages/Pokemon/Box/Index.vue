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
import { Head, setLayoutProps } from '@inertiajs/vue3';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSection from '@/components/AppSection.vue';
import PokemonBox from '@/components/PokemonBox.vue';
import { route } from '@/types/helpers/route';
import type { PokemonBoxIndexPageProps } from '@/types/pageProps/pokemon/BoxIndexPageProps';

defineProps<PokemonBoxIndexPageProps>();

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Box',
      href: route('pokemon.web.box.index'),
    },
    {
      name: 'List',
    },
  ],
});
</script>
