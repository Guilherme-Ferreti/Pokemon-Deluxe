<template>
  <Head :title="page.props.pokemon.name" />
  <div class="flex flex-col gap-4">
    <PokemonHero />
    <div class="grid items-start gap-4 @3xl/main-content:grid-cols-[2fr_3fr]">
      <PokemonStatsCard />
    </div>
  </div>
</template>

<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { route } from '@/shared/types/helpers/route';
import type { PokemonShowPageProps } from '../../../types/page-props/PokemonShowPageProps';
import PokemonHero from './PokemonHero.vue';
import PokemonStatsCard from './PokemonStatsCard.vue';

const page = usePage<PokemonShowPageProps>();

setLayoutProps({
  breadcrumbs: [
    page.props.pokemon.inParty
      ? { name: 'Party', href: route('pokemon.web.party') }
      : { name: 'Box', href: route('pokemon.web.box.index') },
    { name: page.props.pokemon.name },
  ],
});
</script>
