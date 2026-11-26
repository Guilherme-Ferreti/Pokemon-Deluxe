<template>
  <Head :title="page.props.pokemon.name" />
  <AppPageHeader
    :title="page.props.pokemon.name"
    :description="`Level ${page.props.pokemon.level}`"
  />
</template>

<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import type { PokemonShowPageProps } from '../../../types/page-props/PokemonShowPageProps';

const page = usePage<PokemonShowPageProps>();
</script>
