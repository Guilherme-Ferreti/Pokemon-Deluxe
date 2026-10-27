<template>
  <div>
    <ul
      class="grid grid-cols-[repeat(auto-fill,minmax(13rem,1fr))] gap-4"
      data-test="incubators-list"
    >
      <li
        v-for="incubator in page.props.incubators"
        :key="incubator.index"
      >
        <IncubatorNotPurchasedCard
          v-if="incubator.status === IncubatorStatus.NOT_PURCHASED"
          :index="incubator.index"
        />
        <IncubatorIdleCard
          v-else-if="incubator.status === IncubatorStatus.IDLE"
          :index="incubator.index"
        />
        <IncubatorIncubatingCard
          v-else-if="incubator.status === IncubatorStatus.INCUBATING"
          :incubator="incubator"
        />
        <IncubatorReadyToHatchCard
          v-else-if="incubator.status === IncubatorStatus.READY_TO_HATCH"
          :incubator="incubator"
        />
        <IncubatorReadyToCatchCard
          v-else-if="incubator.status === IncubatorStatus.READY_TO_CATCH"
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
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import IncubatorCatchPokemonDialog from '@/app/Pokemon/components/IncubatorCatchPokemonDialog.vue';
import IncubatorIdleCard from '@/app/Pokemon/components/IncubatorIdleCard.vue';
import IncubatorIncubatingCard from '@/app/Pokemon/components/IncubatorIncubatingCard.vue';
import IncubatorNotPurchasedCard from '@/app/Pokemon/components/IncubatorNotPurchasedCard.vue';
import IncubatorReadyToCatchCard from '@/app/Pokemon/components/IncubatorReadyToCatchCard.vue';
import IncubatorReadyToHatchCard from '@/app/Pokemon/components/IncubatorReadyToHatchCard.vue';
import { IncubatorStatus } from '@/app/Pokemon/types/enums/IncubatorStatus';
import type {
  Incubator,
  NurseryPageProps,
} from '@/app/Pokemon/types/page-props/NurseryPageProps';

const page = usePage<NurseryPageProps>();

const catchDialogIsOpen = ref(false);
const incubatorToCatch = ref<Incubator>();

function handleCatchPokemon(incubator: Incubator) {
  incubatorToCatch.value = incubator;
  catchDialogIsOpen.value = true;
}
</script>
