<template>
  <Dialog>
    <DialogTrigger as-child>
      <Button
        type="button"
        variant="outline"
        data-test="pokemon-stats-dialog__open-button"
      >
        <IconChartBar />
        Stats
      </Button>
    </DialogTrigger>
    <DialogContent data-test="pokemon-stats-dialog">
      <DialogHeader>
        <DialogTitle>{{ page.props.pokemon.name }}'s stats</DialogTitle>
        <DialogDescription>
          What each stat is made of: level, boost and shine.
        </DialogDescription>
      </DialogHeader>
      <dl class="flex flex-col divide-y">
        <div
          v-for="row in rows"
          :key="row.key"
          class="flex items-baseline justify-between gap-3 py-3 first:pt-0 last:pb-0"
        >
          <dt class="flex flex-col gap-0.5">
            <span class="text-sm font-medium">{{ row.title }}</span>
            <span
              class="text-xs text-muted-foreground"
              :data-test="`pokemon-stats-dialog__${row.key}-parts-text`"
            >
              {{ row.parts }}
            </span>
          </dt>
          <dd class="flex shrink-0 flex-col items-end">
            <span
              class="text-base font-semibold tabular-nums"
              :data-test="`pokemon-stats-dialog__${row.key}-indicator`"
            >
              {{ row.value }}
            </span>
            <span class="text-xs text-muted-foreground">{{ row.caption }}</span>
          </dd>
        </div>
      </dl>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconChartBar } from '@tabler/icons-vue';
import { computed } from 'vue';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/shared/components/ui/dialog';
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
