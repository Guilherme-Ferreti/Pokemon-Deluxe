<template>
  <Tooltip :disabled="move.slot === null">
    <TooltipTrigger as-child>
      <li
        class="flex min-w-0 flex-col gap-1 rounded-lg border border-dashed p-2"
        :class="
          move.slot === null ? 'cursor-grab' : 'cursor-not-allowed opacity-50'
        "
        :draggable="move.slot === null"
        :tabindex="move.slot === null ? undefined : 0"
        :data-test="`pokemon-known-move-${move.id}-tile`"
        @dragstart="emit('drag-start', move.id)"
        @dragend="emit('drag-end')"
      >
        <MoveNameLabel
          class="truncate text-sm font-medium"
          :name="move.name"
          :is-z-move="move.isZMove"
          :is-dynamax-move="move.isDynamaxMove"
        />
        <PokemonMoveMeta :move="move" />
        <span
          v-if="move.slot !== null"
          class="sr-only"
        >
          Already in slot {{ move.slot }}
        </span>
      </li>
    </TooltipTrigger>
    <TooltipContent>Already in slot {{ move.slot }}</TooltipContent>
  </Tooltip>
</template>

<script setup lang="ts">
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import type { PokemonKnownMove } from '../../../types/page-props/BoxShowPageProps';
import PokemonMoveMeta from './PokemonMoveMeta.vue';

defineProps<{
  move: PokemonKnownMove;
}>();

const emit = defineEmits<{
  'drag-start': [moveId: string];
  'drag-end': [];
}>();
</script>
