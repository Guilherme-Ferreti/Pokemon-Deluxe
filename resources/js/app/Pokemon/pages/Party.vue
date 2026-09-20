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
import PokemonParty from '@/app/Pokemon/components/PokemonParty.vue';
import type { PartyPageProps } from '@/app/Pokemon/types/PartyPageProps';
import AppPageHeader from '@/shared/components/AppPageHeader.vue';
import AppSection from '@/shared/components/AppSection.vue';

defineProps<PartyPageProps>();
</script>
