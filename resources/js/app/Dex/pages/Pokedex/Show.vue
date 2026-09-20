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
  <div class="space-y-4">
    <div
      class="grid gap-4 @2xl/main-content:grid-cols-2 @4xl/main-content:grid-cols-3"
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
    <section>
      <PokedexSpeciesLearnableMoves
        :species-name="species.name"
        :learnable-moves="learnableMoves"
      />
    </section>
  </div>
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import PokedexSpeciesMetadata from '@/app/Dex/components/PokedexSpeciesMetadata.vue';
import PokedexSpeciesNavigation from '@/app/Dex/components/PokedexSpeciesNavigation.vue';
import type { DexPokedexShowPageProps } from '@/app/Dex/types/PokedexShowPageProps';
import PokedexSpeciesAutocomplete from '@/app/Pokemon/components/PokedexSpeciesAutocomplete.vue';
import PokedexSpeciesCaptureStatistics from '@/app/Pokemon/components/PokedexSpeciesCaptureStatistics.vue';
import PokedexSpeciesDamageTaken from '@/app/Pokemon/components/PokedexSpeciesDamageTaken.vue';
import PokedexSpeciesEvolutionChain from '@/app/Pokemon/components/PokedexSpeciesEvolutionChain.vue';
import PokedexSpeciesHero from '@/app/Pokemon/components/PokedexSpeciesHero.vue';
import PokedexSpeciesLearnableMoves from '@/app/Pokemon/components/PokedexSpeciesLearnableMoves.vue';
import AppSection from '@/shared/components/AppSection.vue';
import { route } from '@/shared/types/helpers/route';

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
