<template>
  <span class="flex flex-wrap items-center gap-1 text-xs text-muted-foreground">
    <PokemonTypeBadges :types="[move.type]" />
    <span class="tabular-nums">
      {{ move.power === null ? 'Status' : `Power ${move.power}` }}
    </span>
    <Tooltip v-if="hasStab">
      <TooltipTrigger as-child>
        <Badge
          variant="secondary"
          tabindex="0"
        >
          STAB
        </Badge>
      </TooltipTrigger>
      <TooltipContent>Same type as the Pokémon: x1.5 damage</TooltipContent>
    </Tooltip>
  </span>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import { Badge } from '@/shared/components/ui/badge';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import type {
  PokemonMove,
  PokemonShowPageProps,
} from '../../../types/page-props/PokemonShowPageProps';

const props = defineProps<{
  move: PokemonMove;
}>();

const page = usePage<PokemonShowPageProps>();

const hasStab = computed(
  () =>
    props.move.power !== null &&
    (page.props.pokemon.species.types as string[]).includes(props.move.type),
);
</script>
