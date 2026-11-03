<template>
  <AppInfoCard
    title="Damage Taken"
    data-test="damage-taken-card"
    :description="`The effectiveness of each type on ${page.props.species.name}`"
  >
    <template
      v-for="(row, index) in rows"
      :key="row.multiplier"
    >
      <div class="flex justify-between gap-6">
        <AppInfoCardSectionTitle :title="row.multiplier" />
        <div
          v-if="row.types.length > 0"
          class="flex w-full flex-wrap justify-end gap-2"
        >
          <PokemonTypeBadges
            v-for="type in row.types"
            :key="type"
            :types="[type]"
          />
        </div>
        <span
          v-else
          class="text-sm text-muted-foreground"
        >
          None
        </span>
      </div>
      <Separator v-if="index < rows.length - 1" />
    </template>
  </AppInfoCard>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppInfoCard from '@/shared/components/content/AppInfoCard.vue';
import AppInfoCardSectionTitle from '@/shared/components/content/AppInfoCardSectionTitle.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import Separator from '@/shared/components/ui/separator/Separator.vue';
import type { PokemonType } from '@/shared/types/enums';
import type { PokedexShowPageProps } from '../../../types/page-props/PokedexShowPageProps';

const page = usePage<PokedexShowPageProps>();

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

function getTypesForMultiplier(multiplier: number): PokemonType[] {
  return Object.entries(page.props.damageTaken)
    .filter(([, value]) => value === multiplier)
    .map(([key]) => key) as PokemonType[];
}
</script>
