<template>
  <ul class="grid grid-cols-[repeat(auto-fill,minmax(13rem,1fr))] gap-4">
    <li
      v-for="incubatorIndex in page.props.game.maxEggIncubatorsPerUser"
      :key="incubatorIndex"
    >
      <EggIncubatorNotPurchasedCard
        v-if="incubatorAvailability.notPurchased.includes(incubatorIndex)"
        :egg-incubator-index="incubatorIndex"
      />
      <EggIncubatorActiveCard
        v-else-if="incubatorAvailability.active.includes(incubatorIndex)"
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
import { usePage } from '@inertiajs/vue3';
import EggIncubatorActiveCard from '@/app/Pokemon/components/EggIncubatorActiveCard.vue';
import EggIncubatorIdleCard from '@/app/Pokemon/components/EggIncubatorIdleCard.vue';
import EggIncubatorNotPurchasedCard from '@/app/Pokemon/components/EggIncubatorNotPurchasedCard.vue';
import type { NurseryPageProps } from '@/app/Pokemon/types/page-props/NurseryPageProps';

const props = defineProps<{
  activeEggIncubations: NurseryPageProps['activeEggIncubations'];
  incubatorAvailability: NurseryPageProps['incubatorAvailability'];
}>();

const page = usePage();

function getEggIncubationAtIndex(index: number) {
  return props.activeEggIncubations.find(
    (process) => process.eggIncubatorIndex === index,
  ) as NurseryPageProps['activeEggIncubations'][number];
}
</script>
