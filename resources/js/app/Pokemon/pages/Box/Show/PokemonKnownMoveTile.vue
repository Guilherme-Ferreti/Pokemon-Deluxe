<template>
  <li
    class="flex min-w-0 flex-col gap-0.5 rounded-lg border border-dashed p-2"
    :class="move.slot === null ? 'cursor-grab' : 'opacity-50'"
    :draggable="move.slot === null"
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
      class="text-xs text-muted-foreground"
    >
      In slot {{ move.slot }}
    </span>
  </li>
</template>

<script setup lang="ts">
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
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
