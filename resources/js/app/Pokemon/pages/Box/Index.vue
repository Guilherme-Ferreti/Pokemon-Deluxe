<template>
  <Head title="Your Box" />
  <AppPageHeader
    title="Your Box"
    description="Manage your caught Pokemon."
  />
  <AppSection>
    <PokemonBox />
  </AppSection>
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import PokemonBox from '@/app/Pokemon/components/PokemonBox.vue';
import AppSection from '@/shared/components/content/AppSection.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import { route } from '@/shared/types/helpers/route';

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
