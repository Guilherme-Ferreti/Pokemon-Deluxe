<template>
  <Head :title="page.props.species.name" />
  <AppSection
    class="flex-wrap items-center justify-center @3xl/main-content:flex-row @3xl/main-content:justify-between"
  >
    <SpeciesSearchInput class="w-full max-w-89" />
    <NavigationLinks />
  </AppSection>
  <div class="space-y-4">
    <div
      class="grid gap-4 @2xl/main-content:grid-cols-2 @4xl/main-content:grid-cols-3"
    >
      <section class="@2xl/main-content:col-span-2">
        <SpeciesHero class="h-full" />
      </section>
      <section>
        <OverviewCard class="h-full" />
      </section>
      <section class="@4xl/main-content:order-1">
        <EvolutionChain class="h-full @4xl/main-content:h-auto" />
      </section>
      <section>
        <DamageTakenCard />
      </section>
      <section>
        <CatchStatisticsCard />
      </section>
    </div>
    <section>
      <LearnableMovesCard />
    </section>
  </div>
</template>

<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import CatchStatisticsCard from '@/app/Dex/pages/Pokedex/Show/CatchStatisticsCard.vue';
import DamageTakenCard from '@/app/Dex/pages/Pokedex/Show/DamageTakenCard.vue';
import EvolutionChain from '@/app/Dex/pages/Pokedex/Show/EvolutionChain.vue';
import LearnableMovesCard from '@/app/Dex/pages/Pokedex/Show/LearnableMovesCard.vue';
import NavigationLinks from '@/app/Dex/pages/Pokedex/Show/NavigationLinks.vue';
import OverviewCard from '@/app/Dex/pages/Pokedex/Show/OverviewCard.vue';
import SpeciesHero from '@/app/Dex/pages/Pokedex/Show/SpeciesHero.vue';
import SpeciesSearchInput from '@/app/Dex/pages/Pokedex/Show/SpeciesSearchInput.vue';
import type { PokedexShowPageProps } from '@/app/Dex/types/page-props/PokedexShowPageProps';
import AppSection from '@/shared/components/content/AppSection.vue';
import { route } from '@/shared/types/helpers/route';

const page = usePage<PokedexShowPageProps>();

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Pokédex',
      href: route('dex.web.pokedex.index'),
    },
    {
      name: page.props.species.name,
      href: route('dex.web.pokedex.show', {
        species: page.props.species.slug,
      }),
    },
    {
      name: 'View',
    },
  ],
});
</script>
