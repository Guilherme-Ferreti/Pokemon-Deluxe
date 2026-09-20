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
import PokemonBox from '@/app/Pokemon/components/PokemonBox.vue';
import type { PokemonBoxIndexPageProps } from '@/app/Pokemon/types/BoxIndexPageProps';
import AppPageHeader from '@/shared/components/AppPageHeader.vue';
import AppSection from '@/shared/components/AppSection.vue';
import { route } from '@/shared/types/helpers/route';

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
