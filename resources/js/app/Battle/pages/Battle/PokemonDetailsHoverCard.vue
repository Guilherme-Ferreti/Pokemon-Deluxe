<template>
  <HoverCardContent
    class="flex w-70 flex-col gap-2.5 rounded-xl p-3.5"
    data-test="pokemon-details-hover-card"
    side="top"
    align="start"
    :side-offset="8"
  >
    <p class="flex items-start gap-1.5 text-sm">
      <PokemonDisplayName
        class="min-w-0 flex-1 leading-snug font-semibold wrap-anywhere"
        :name="pokemon.name"
        :gender="pokemon.gender"
      />
      <span
        class="text-xs leading-5 whitespace-nowrap text-muted-foreground tabular-nums"
      >
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
