<template>
  <Head title="Your Party" />
  <AppPageHeader
    title="Your Party"
    description="Manage your party."
  />
  <AppSection>
    <PokemonParty :party="party" />
  </AppSection>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSection from '@/components/AppSection.vue';
import PokemonParty from '@/components/PokemonParty.vue';

export interface PokemonPartyPageProps {
  party: {
    id: string;
    name: string;
    frontSprite: string | null;
    level: number;
  }[];
}

defineProps<PokemonPartyPageProps>();
</script>
