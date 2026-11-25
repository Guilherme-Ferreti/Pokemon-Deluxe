<template>
  <div class="flex flex-col gap-2.5">
    <PokemonTypeBadges :types="pokemon.types" />
    <div class="flex flex-col gap-1">
      <p
        class="font-semibold"
        :class="isLarge ? 'text-sm' : 'text-xs'"
      >
        Stat bonuses
      </p>
      <ul
        class="ml-4 flex list-disc flex-col gap-0.5 text-muted-foreground"
        :class="isLarge ? 'text-sm' : 'text-xs'"
      >
        <li
          v-for="stat in statBonuses"
          :key="stat.name"
        >
          <strong class="font-semibold text-foreground">
            {{ formatSignedPercentage(stat.percentage) }}
          </strong>
          {{ stat.name }}
        </li>
      </ul>
    </div>
    <ul
      class="grid grid-cols-2 gap-x-2.5 gap-y-1.5 border-t border-border pt-2.5"
    >
      <li
        v-for="(move, index) in pokemon.moves"
        :key="index"
        class="flex min-w-0 flex-col"
      >
        <span
          class="font-medium"
          :class="isLarge ? 'text-sm' : 'text-xs'"
        >
          {{ move.name }}
        </span>
        <span
          class="text-muted-foreground"
          :class="isLarge ? 'text-xs' : 'text-xxs'"
        >
          {{ $page.props.labels.pokemonType[move.type] }} ·
          {{ move.power ?? 'Status' }}
        </span>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import type { BattlePokemonDetails } from '../../types/page-props/BattleShowPageProps';

const props = withDefaults(
  defineProps<{
    pokemon: BattlePokemonDetails;
    isLarge?: boolean;
  }>(),
  {
    isLarge: false,
  },
);

const statBonuses = computed(() => [
  { name: 'HP', percentage: props.pokemon.statBonuses.hp },
  { name: 'attack', percentage: props.pokemon.statBonuses.attack },
  { name: 'damage taken', percentage: props.pokemon.statBonuses.damageTaken },
]);

function formatSignedPercentage(percentage: number): string {
  return percentage < 0 ? `−${Math.abs(percentage)}%` : `+${percentage}%`;
}
</script>
