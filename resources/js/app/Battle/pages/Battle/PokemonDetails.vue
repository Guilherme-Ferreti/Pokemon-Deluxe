<template>
  <div :class="['pokemon-details', { 'pokemon-details--large': isLarge }]">
    <PokemonTypeBadges :types="pokemon.types" />
    <p
      v-if="pokemon.boost > 0"
      class="pokemon-details__effect"
    >
      In
      <strong class="pokemon-details__effect-name">
        Boost +{{ pokemon.boost }}
      </strong>
      · +{{ pokemon.boost }}% HP and damage
    </p>
    <p
      v-if="pokemon.isShiny"
      class="pokemon-details__effect"
    >
      <strong class="pokemon-details__effect-name">Shiny</strong>
      · +25% HP and damage, takes 25% less damage
    </p>
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
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import type { BattlePokemonDetails } from '../../types/page-props/BattleShowPageProps';

withDefaults(
  defineProps<{
    pokemon: BattlePokemonDetails;
    isLarge?: boolean;
  }>(),
  {
    isLarge: false,
  },
);
</script>

<style scoped>
@reference '@css/app.css';

.pokemon-details {
  @apply flex flex-col gap-2.5;
}

.pokemon-details__effect {
  @apply text-xs leading-snug text-muted-foreground;
}

.pokemon-details--large .pokemon-details__effect {
  @apply text-sm;
}

.pokemon-details__effect-name {
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
