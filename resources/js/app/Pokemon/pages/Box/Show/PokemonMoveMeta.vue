<template>
  <span class="flex flex-wrap items-center gap-1 text-xs text-muted-foreground">
    <PokemonTypeBadges :types="[move.type]" />
    <span class="tabular-nums">
      {{ move.power === null ? 'Status' : `Power ${move.power}` }}
    </span>
    <Tooltip v-if="hasStab">
      <TooltipTrigger as-child>
        <span
          class="cursor-help underline decoration-dotted underline-offset-2"
          tabindex="0"
        >
          STAB
        </span>
      </TooltipTrigger>
      <TooltipContent>Same type as the Pokémon: x1.5 damage</TooltipContent>
    </Tooltip>
  </span>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import type {
  PokemonMove,
  BoxShowPageProps,
} from '../../../types/page-props/BoxShowPageProps';

const props = defineProps<{
  move: PokemonMove;
}>();

const page = usePage<BoxShowPageProps>();

const hasStab = computed(
  () =>
    props.move.power !== null &&
    (page.props.pokemon.species.types as string[]).includes(props.move.type),
);
</script>
