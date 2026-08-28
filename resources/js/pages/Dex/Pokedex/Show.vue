<template>
  <Head :title="species.name" />
  <AppSection class="flex-row flex-wrap justify-between">
    <AppPageHeader
      :title="species.name"
      :description="`The ${species.genus}`"
    />
    <PokedexSpeciesNavigation
      :previous-species="previousSpecies"
      :next-species="nextSpecies"
    />
  </AppSection>
  <AppSection>
    <AppSpeciesSprite
      :url="species.sprites.frontDefault"
      :name="species.name"
    />
    <p>{{ species.textEntry }}</p>
  </AppSection>
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSection from '@/components/AppSection.vue';
import AppSpeciesSprite from '@/components/AppSpeciesSprite.vue';
import PokedexSpeciesNavigation from '@/components/PokedexSpeciesNavigation.vue';
import type { SpeciesSprites, SpeciesTypes } from '@/types';
import { route } from '@/types/helpers/route';

export interface DexPokedexShowPageProps {
  species: {
    id: string;
    name: string;
    types: SpeciesTypes;
    pokedexNumber: number;
    genus: string;
    textEntry: string;
    sprites: SpeciesSprites;
    portrait: string;
    slug: string;
  };
  previousSpecies?: {
    name: string;
    pokedexNumber: number;
    portrait: string;
    slug: string;
  };
  nextSpecies?: {
    name: string;
    pokedexNumber: number;
    portrait: string;
    slug: string;
  };
}

const props = defineProps<DexPokedexShowPageProps>();

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Pokédex',
      href: route('web.dex.pokedex.index'),
    },
    {
      name: props.species.name,
      href: route('web.dex.pokedex.show', { species: props.species.slug }),
    },
    {
      name: 'View',
    },
  ],
});
</script>
