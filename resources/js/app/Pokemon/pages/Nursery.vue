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
    <ul class="grid grid-cols-[repeat(auto-fill,minmax(13rem,1fr))] gap-4">
      <li
        v-for="incubatorIndex in maxEggIncubatorsCount"
        :key="incubatorIndex"
      >
        <EggIncubatorActiveCard
          v-if="incubatorAvailability.inUse.includes(incubatorIndex)"
          :egg-incubation-process="
            getEggIncubationProcessAtIndex(incubatorIndex)
          "
        />
      </li>
    </ul>
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
import EggIncubatorActiveCard from '@/app/Pokemon/components/EggIncubatorActiveCard.vue';
import type { NurseryPageProps } from '@/app/Pokemon/types/page-props/NurseryPageProps';
import AppPageHeader from '@/shared/components/AppPageHeader.vue';
import AppSection from '@/shared/components/AppSection.vue';
import AppSectionHeading from '@/shared/components/AppSectionHeading.vue';

const props = defineProps<NurseryPageProps>();

const maxEggIncubatorsCount = 10;

function getEggIncubationProcessAtIndex(index: number) {
  return props.activeEggIncubationProcesses.find(
    (process) => process.eggIncubatorIndex === index,
  ) as NurseryPageProps['activeEggIncubationProcesses'][number];
}
</script>
