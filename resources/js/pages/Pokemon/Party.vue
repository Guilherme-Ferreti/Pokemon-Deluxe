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
import type { PokemonPartyPageProps } from '@/types/pageProps/pokemon/PartyPageProps';

defineProps<PokemonPartyPageProps>();
</script>
