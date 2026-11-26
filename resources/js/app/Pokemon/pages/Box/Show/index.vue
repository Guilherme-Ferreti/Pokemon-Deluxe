<template>
  <Head :title="page.props.pokemon.name" />
  <div class="flex flex-col gap-4">
    <PokemonHero />
    <PokemonMovesCard />
  </div>
</template>

<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { route } from '@/shared/types/helpers/route';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';
import PokemonHero from './PokemonHero.vue';
import PokemonMovesCard from './PokemonMovesCard.vue';

const page = usePage<BoxShowPageProps>();

setLayoutProps({
  breadcrumbs: [
    { name: 'Box', href: route('pokemon.web.box.index') },
    {
      name: page.props.pokemon.name,
      href: route('pokemon.web.box.show', { pokemon: page.props.pokemon.id }),
    },
    { name: 'View' },
  ],
});
</script>
