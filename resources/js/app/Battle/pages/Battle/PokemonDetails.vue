<template>
  <div :class="['pokemon-details', { 'pokemon-details--large': isLarge }]">
    <PokemonTypeBadges :types="pokemon.types" />
    <div class="pokemon-details__stats">
      <p class="pokemon-details__stats-title">Stat bonuses</p>
      <ul class="pokemon-details__stats-list">
        <li
          v-for="stat in statBonuses"
          :key="stat.name"
          class="pokemon-details__stat"
        >
          <strong class="pokemon-details__stat-value">
            {{ formatSignedPercentage(stat.percentage) }}
          </strong>
          {{ stat.name }}
        </li>
      </ul>
    </div>
    <ul class="pokemon-details__moves">
      <li
        v-for="(move, index) in pokemon.moves"
        :key="index"
        class="pokemon-details__move"
      >
        <span class="pokemon-details__move-name">{{ move.name }}</span>
        <span class="pokemon-details__move-detail">
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

<style scoped>
@reference '@css/app.css';

.pokemon-details {
  @apply flex flex-col gap-2.5;
}

.pokemon-details__stats {
  @apply flex flex-col gap-1;
}

.pokemon-details__stats-title {
  @apply text-xs font-semibold;
}

.pokemon-details--large .pokemon-details__stats-title {
  @apply text-sm;
}

.pokemon-details__stats-list {
  @apply ml-4 flex list-disc flex-col gap-0.5 text-xs text-muted-foreground;
}

.pokemon-details--large .pokemon-details__stats-list {
  @apply text-sm;
}

.pokemon-details__stat-value {
  @apply font-semibold text-foreground;
}

.pokemon-details__moves {
  @apply grid grid-cols-2 gap-x-2.5 gap-y-1.5 border-t border-border pt-2.5;
}

.pokemon-details__move {
  @apply flex min-w-0 flex-col;
}

.pokemon-details__move-name {
  @apply text-xs font-medium;
}

.pokemon-details--large .pokemon-details__move-name {
  @apply text-sm;
}

.pokemon-details__move-detail {
  @apply text-xxs text-muted-foreground;
}

.pokemon-details--large .pokemon-details__move-detail {
  @apply text-xs;
}
</style>
