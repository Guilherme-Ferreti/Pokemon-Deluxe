<template>
  <Head title="Your Pokemon" />
  <AppPageHeader
    title="Your Pokemon"
    description="Manage your captured Pokemon and party."
  />
  <PartyCard :pokemon-in-party="pokemonInParty" />
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppPageHeader from '@/components/AppPageHeader.vue';
import PartyCard from '@/components/PartyCard.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { route } from '@/types/helpers/route';

defineProps<{
  pokemonInParty: {
    id: string;
    name: string;
    frontSprite?: string;
    level: number;
  }[];
}>();

const { setBreadcrumbs } = useBreadcrumbs();
setBreadcrumbs([
  { title: 'Pokemon', href: route('web.pokemon.index') },
  { title: 'List' },
]);
</script>
