<template>
  <AppInfoCard
    title="Stats"
    data-test="pokemon-stats-card"
  >
    <div
      v-for="row in rows"
      :key="row.key"
      class="flex items-baseline justify-between gap-3"
    >
      <span class="text-sm font-medium text-muted-foreground">
        {{ row.title }}
      </span>
      <Tooltip>
        <TooltipTrigger as-child>
          <span
            class="flex cursor-default flex-col items-end"
            tabindex="0"
          >
            <span
              class="text-base font-semibold tabular-nums"
              :data-test="`pokemon-stats-card__${row.key}-indicator`"
            >
              {{ row.value }}
            </span>
            <span class="text-xs text-muted-foreground">{{ row.caption }}</span>
          </span>
        </TooltipTrigger>
        <TooltipContent>{{ row.parts }}</TooltipContent>
      </Tooltip>
    </div>
  </AppInfoCard>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppInfoCard from '@/shared/components/content/AppInfoCard.vue';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import type {
  BoxShowPageProps,
  PokemonStat,
} from '../../../types/page-props/BoxShowPageProps';

const page = usePage<BoxShowPageProps>();

const formatMultiplier = (multiplier: number) =>
  `x${multiplier.toLocaleString('en-US', { maximumFractionDigits: 2 })}`;

const formatPercentageChange = (multiplier: number) => {
  const percentage = Math.round((multiplier - 1) * 1000) / 10;

  return percentage < 0 ? `−${Math.abs(percentage)}%` : `+${percentage}%`;
};

const describeParts = (stat: PokemonStat, base: string) =>
  [
    `Level ${page.props.pokemon.level}: ${base}`,
    `Boost +${page.props.pokemon.boost}: ${formatMultiplier(stat.boostMultiplier)}`,
    stat.shinyMultiplier === null
      ? null
      : `Shiny: ${formatMultiplier(stat.shinyMultiplier)}`,
  ]
    .filter((part) => part !== null)
    .join(' · ');

const rows = computed(() => {
  const { hp, attack, damageTaken } = page.props.stats;

  return [
    {
      key: 'hp',
      title: 'HP',
      value: hp.value.toLocaleString('en-US'),
      caption: 'Max HP',
      parts: describeParts(hp, hp.base.toLocaleString('en-US')),
    },
    {
      key: 'attack',
      title: 'Attack',
      value: formatMultiplier(attack.value),
      caption: 'Damage multiplier',
      parts: describeParts(attack, formatMultiplier(attack.base)),
    },
    {
      key: 'defense',
      title: 'Defense',
      value: formatPercentageChange(damageTaken.value),
      caption: 'Damage taken',
      parts: describeParts(damageTaken, 'x1'),
    },
  ];
});
</script>
