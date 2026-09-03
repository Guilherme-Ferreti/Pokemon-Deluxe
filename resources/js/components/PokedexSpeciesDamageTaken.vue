<template>
  <AppInfoCard
    title="Damage Taken"
    :description="`The effectiveness of each type on ${speciesName}`"
  >
    <template
      v-for="(row, index) in rows"
      :key="row.multiplier"
    >
      <div class="flex justify-between gap-6">
        <span class="text-sm font-bold">{{ row.multiplier }}</span>
        <div
          v-if="row.types.length > 0"
          class="flex w-full flex-wrap justify-end gap-2"
        >
          <ElementalTypeBadges
            v-for="type in row.types"
            :key="type"
            :types="[type]"
          />
        </div>
        <span
          v-else
          class="text-sm text-foreground"
        >
          None
        </span>
      </div>
      <Separator v-if="index < rows.length - 1" />
    </template>
  </AppInfoCard>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { DexPokedexShowPageProps } from '@/pages/Dex/Pokedex/Show.vue';
import type { ElementalType } from '@/types/php-types.js';
import AppInfoCard from './AppInfoCard.vue';
import ElementalTypeBadges from './ElementalTypeBadges.vue';
import Separator from './ui/separator/Separator.vue';

const props = defineProps<{
  speciesName: string;
  damageTaken: DexPokedexShowPageProps['damageTaken'];
}>();

const rows = computed(() => [
  {
    multiplier: 'x4',
    types: getTypesForMultiplier(4.0),
  },
  {
    multiplier: 'x2',
    types: getTypesForMultiplier(2.0),
  },
  {
    multiplier: 'x1',
    types: getTypesForMultiplier(1.0),
  },
  {
    multiplier: 'x0.5',
    types: getTypesForMultiplier(0.5),
  },
  {
    multiplier: 'x0.25',
    types: getTypesForMultiplier(0.25),
  },
  {
    multiplier: 'x0',
    types: getTypesForMultiplier(0),
  },
]);

function getTypesForMultiplier(multiplier: number): ElementalType[] {
  return Object.entries(props.damageTaken)
    .filter(([, value]) => value === multiplier)
    .map(([key]) => key) as ElementalType[];
}
</script>
