<template>
  <Head :title="species.name" />
  <AppSection
    class="flex-wrap items-center justify-center @3xl/main-content:flex-row @3xl/main-content:justify-between"
  >
    <PokedexSpeciesAutocomplete class="w-full max-w-89" />
    <PokedexSpeciesNavigation
      :previous-species="previousSpecies"
      :next-species="nextSpecies"
    />
  </AppSection>
  <div
    class="grid gap-6 @2xl/main-content:grid-cols-2 @4xl/main-content:grid-cols-3"
  >
    <section class="@2xl/main-content:col-span-2">
      <PokedexSpeciesHero
        class="h-full"
        :species="species"
      />
    </section>
    <section>
      <PokedexSpeciesMetadata
        class="h-full"
        :species="species"
      />
    </section>
    <section class="@4xl/main-content:order-1">
      <PokedexSpeciesEvolutionChain
        class="h-full @4xl/main-content:h-auto"
        :evolution-chain="evolutionChain"
      />
    </section>
    <section>
      <PokedexSpeciesDamageTaken
        :species-name="species.name"
        :damage-taken="damageTaken"
      />
    </section>
    <section>
      <PokedexSpeciesCaptureStatistics
        :species-name="species.name"
        :statistics="captureStatistics"
      />
    </section>
  </div>
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import AppSection from '@/components/AppSection.vue';
import PokedexSpeciesAutocomplete from '@/components/PokedexSpeciesAutocomplete.vue';
import PokedexSpeciesCaptureStatistics from '@/components/PokedexSpeciesCaptureStatistics.vue';
import PokedexSpeciesDamageTaken from '@/components/PokedexSpeciesDamageTaken.vue';
import PokedexSpeciesEvolutionChain from '@/components/PokedexSpeciesEvolutionChain.vue';
import PokedexSpeciesHero from '@/components/PokedexSpeciesHero.vue';
import PokedexSpeciesMetadata from '@/components/PokedexSpeciesMetadata.vue';
import PokedexSpeciesNavigation from '@/components/PokedexSpeciesNavigation.vue';
import type {
  ElementalType,
  PokemonGender,
  SpeciesSprites,
  SpeciesTypes,
} from '@/types';
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
    isGenderless: boolean;
    malePercentage: number;
    femalePercentage: number;
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
  captureStatistics: {
    currentUser: {
      regular: number;
      shiny: number;
    };
    global: {
      regular: number;
      shiny: number;
    };
  };
  damageTaken: Record<ElementalType, number>;
  evolutionChain: {
    previous: {
      species: {
        name: string;
        slug: string;
        portrait: string;
      };
      evolutionItem?: {
        name: string;
        sprite?: string;
      };
      minLevel?: number;
      gender?: PokemonGender;
    }[];
    into: {
      species: {
        name: string;
        slug: string;
        portrait: string;
      };
      evolutionItem?: {
        name: string;
        sprite?: string;
      };
      minLevel?: number;
      gender?: PokemonGender;
    }[];
  };
}

const props = defineProps<DexPokedexShowPageProps>();

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Pokédex',
      href: route('dex.web.pokedex.index'),
    },
    {
      name: props.species.name,
      href: route('dex.web.pokedex.show', { species: props.species.slug }),
    },
    {
      name: 'View',
    },
  ],
});
</script>
