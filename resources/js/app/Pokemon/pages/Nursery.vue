<template>
  <Head title="Nursery" />
  <AppPageHeader
    title="Nursery"
    description="Manage your Pokémon eggs."
  />
  <AppSection>
    <AppSectionHeading
      title="Your incubators"
      description="Click on an empty incubator to place an egg, then hatch it when it is ready."
    />
    <EggIncubatorsList />
    />
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
import EggIncubatorsList from '@/app/Pokemon/components/EggIncubatorsList.vue';
import AppPageHeader from '@/shared/components/AppPageHeader.vue';
import AppSection from '@/shared/components/AppSection.vue';
import AppSectionHeading from '@/shared/components/AppSectionHeading.vue';
</script>
