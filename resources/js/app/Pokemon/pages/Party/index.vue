<template>
  <Head title="Your Party" />
  <AppPageHeader
    title="Your Party"
    description="Manage your party."
  />
  <AppSection>
    <PokemonParty />
  </AppSection>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PokemonParty from '@/app/Pokemon/components/PokemonParty.vue';
import AppSection from '@/shared/components/content/AppSection.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
</script>
