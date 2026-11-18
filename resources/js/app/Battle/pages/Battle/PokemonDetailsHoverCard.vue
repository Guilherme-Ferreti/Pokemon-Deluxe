<template>
  <HoverCardContent
    class="pokemon-details-hover-card"
    data-test="pokemon-details-hover-card"
    side="top"
    align="start"
    :side-offset="8"
  >
    <p class="pokemon-details-hover-card__title">
      <PokemonDisplayName
        class="pokemon-details-hover-card__name"
        :name="pokemon.name"
        :gender="pokemon.gender"
      />
      <span class="pokemon-details-hover-card__level">
        Lv {{ pokemon.level }}
      </span>
    </p>
    <PokemonDetails :pokemon="pokemon" />
  </HoverCardContent>
</template>

<script setup lang="ts">
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import { HoverCardContent } from '@/shared/components/ui/hover-card';
import type { BattlePokemonDetails } from '../../types/page-props/BattleShowPageProps';
import PokemonDetails from './PokemonDetails.vue';

defineProps<{
  pokemon: BattlePokemonDetails;
}>();
</script>

<style scoped>
@reference '@css/app.css';

:global(.pokemon-details-hover-card) {
  @apply flex w-70 flex-col gap-2.5 rounded-xl p-3.5;
}

.pokemon-details-hover-card__title {
  @apply flex items-start gap-1.5 text-sm;
}

.pokemon-details-hover-card__name {
  @apply min-w-0 flex-1 leading-snug font-semibold wrap-anywhere;
}

.pokemon-details-hover-card__level {
  @apply text-xs leading-5 whitespace-nowrap text-muted-foreground tabular-nums;
}
</style>
