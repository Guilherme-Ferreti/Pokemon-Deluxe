<template>
  <Head :title="species.name" />
  <AppSection>
    <PokedexSpeciesNavigation
      class="m-auto"
      :previous-species="previousSpecies"
      :next-species="nextSpecies"
    />
  </AppSection>
  <AppSection class="grid lg:grid-cols-[2fr_1fr]">
    <PokedexSpeciesHero :species="species" />
    <PokedexSpeciesMetadata :species="species" />
  </AppSection>
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import AppSection from '@/components/AppSection.vue';
import PokedexSpeciesHero from '@/components/PokedexSpeciesHero.vue';
import PokedexSpeciesMetadata from '@/components/PokedexSpeciesMetadata.vue';
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
    portraits: {
      regular?: string;
      shiny?: string;
    };
    traits: {
      isBaby: boolean;
      isLegendary: boolean;
      isMythical: boolean;
      isMega: boolean;
      isGmax: boolean;
    };
    generation: string;
    slug: string;
    isCapturable: boolean;
    captureRatePercentage: number;
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
