<template>
  <Head :title="page.props.pokemon.name" />
  <div class="flex flex-col gap-4">
    <PokemonHero />
    <div class="grid items-start gap-4 @3xl/main-content:grid-cols-[2fr_3fr]">
      <PokemonStatsCard />
      <PokemonMovesCard />
    </div>
  </div>
</template>

<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { route } from '@/shared/types/helpers/route';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';
import PokemonHero from './PokemonHero.vue';
import PokemonMovesCard from './PokemonMovesCard.vue';
import PokemonStatsCard from './PokemonStatsCard.vue';

const page = usePage<BoxShowPageProps>();

setLayoutProps({
  breadcrumbs: [
    { name: 'Box', href: route('pokemon.web.box.index') },
    { name: page.props.pokemon.name },
  ],
});
</script>
