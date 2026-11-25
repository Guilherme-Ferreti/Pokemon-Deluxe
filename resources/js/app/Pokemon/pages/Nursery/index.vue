<template>
  <Head title="Nursery" />
  <AppPageHeader
    title="Nursery"
    description="Manage your Pokémon eggs."
  >
    <PlayerMoney />
  </AppPageHeader>
  <AppSection>
    <AppSectionHeading
      title="Your incubators"
      description="Click on an empty incubator to place an egg, then hatch it when it is ready."
    />
    <IncubatorsGrid />
  </AppSection>
  <AppSection>
    <AppSectionHeading
      title="History"
      description="See the history of all Pokémon born in your nursery."
    />
  </AppSection>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppSection from '@/shared/components/content/AppSection.vue';
import AppSectionHeading from '@/shared/components/content/AppSectionHeading.vue';
import PlayerMoney from '@/shared/components/game/PlayerMoney.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import IncubatorsGrid from './IncubatorsGrid.vue';
</script>
