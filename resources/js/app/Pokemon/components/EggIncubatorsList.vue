<template>
  <ul class="grid grid-cols-[repeat(auto-fill,minmax(13rem,1fr))] gap-4">
    <li
      v-for="incubatorIndex in maxEggIncubatorsCount"
      :key="incubatorIndex"
    >
      <EggIncubatorActiveCard
        v-if="incubatorAvailability.active.includes(incubatorIndex)"
        :egg-incubation="getEggIncubationAtIndex(incubatorIndex)"
      />
      <EggIncubatorIdleCard
        v-else-if="incubatorAvailability.idle.includes(incubatorIndex)"
        :egg-incubator-index="incubatorIndex"
      />
    </li>
  </ul>
</template>

<script setup lang="ts">
import EggIncubatorActiveCard from '@/app/Pokemon/components/EggIncubatorActiveCard.vue';
import EggIncubatorIdleCard from '@/app/Pokemon/components/EggIncubatorIdleCard.vue';
import type { NurseryPageProps } from '@/app/Pokemon/types/page-props/NurseryPageProps';

const props = defineProps<{
  activeEggIncubations: NurseryPageProps['activeEggIncubations'];
  incubatorAvailability: NurseryPageProps['incubatorAvailability'];
}>();

const maxEggIncubatorsCount = 10;

function getEggIncubationAtIndex(index: number) {
  return props.activeEggIncubations.find(
    (process) => process.eggIncubatorIndex === index,
  ) as NurseryPageProps['activeEggIncubations'][number];
}
</script>
