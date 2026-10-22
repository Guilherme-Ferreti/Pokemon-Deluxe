<template>
  <ul class="grid grid-cols-[repeat(auto-fill,minmax(13rem,1fr))] gap-4">
    <li
      v-for="incubator in page.props.incubators"
      :key="incubator.index"
    >
      <IncubatorNotPurchasedCard
        v-if="incubator.status === 'not-purchased'"
        :index="incubator.index"
      />
      <IncubatorIdleCard
        v-else-if="incubator.status === 'idle'"
        :index="incubator.index"
      />
      <IncubatorIncubatingCard
        v-else-if="incubator.status === 'incubating'"
        :incubator="incubator"
      />
      <IncubatorReadyToHatchCard
        v-else-if="incubator.status === 'ready-to-hatch'"
        :incubator="incubator"
      />
      <IncubatorReadyToCatchCard
        v-else-if="incubator.status === 'ready-to-catch'"
        :incubator="incubator"
        @catch-pokemon="handleCatchPokemon(incubator)"
      />
    </li>
  </ul>
  <IncubatorCatchPokemonDialog
    v-if="incubatorToCatch"
    v-model:is-open="catchDialogIsOpen"
    :incubator="incubatorToCatch"
  />
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import IncubatorIdleCard from '@/app/Pokemon/components/IncubatorIdleCard.vue';
import IncubatorIncubatingCard from '@/app/Pokemon/components/IncubatorIncubatingCard.vue';
import IncubatorNotPurchasedCard from '@/app/Pokemon/components/IncubatorNotPurchasedCard.vue';
import IncubatorReadyToCatchCard from '@/app/Pokemon/components/IncubatorReadyToCatchCard.vue';
import IncubatorReadyToHatchCard from '@/app/Pokemon/components/IncubatorReadyToHatchCard.vue';
import type {
  Incubator,
  NurseryPageProps,
} from '@/app/Pokemon/types/page-props/NurseryPageProps';
import IncubatorCatchPokemonDialog from './IncubatorCatchPokemonDialog.vue';

const page = usePage<NurseryPageProps>();

const catchDialogIsOpen = ref(false);
const incubatorToCatch = ref<Incubator>();

function handleCatchPokemon(incubator: Incubator) {
  incubatorToCatch.value = incubator;
  catchDialogIsOpen.value = true;
}
</script>
