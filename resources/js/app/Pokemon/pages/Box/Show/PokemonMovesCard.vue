<template>
  <Card data-test="pokemon-moves-card">
    <CardHeader class="flex flex-wrap items-start justify-between gap-2">
      <div class="flex flex-col gap-1">
        <CardTitle>Active moves</CardTitle>
        <CardDescription>
          The order is the order of the move buttons in battle.
        </CardDescription>
      </div>
      <PokemonTeachTmDialog />
    </CardHeader>
    <CardContent class="flex flex-col gap-4">
      <ol
        class="flex flex-col gap-1.5"
        data-test="pokemon-moves-card__slot-list"
      >
        <PokemonMoveSlot
          v-for="slot in slots"
          :key="slot.number"
          :move-slot="slot"
          @swap="slotToSwap = slot.number"
          @drag-start="(moveId) => startDragging('slot', moveId)"
          @drag-end="draggedMove = null"
          @drop="dropOnSlot(slot)"
        />
      </ol>
      <div class="flex flex-wrap items-baseline justify-between gap-2">
        <h3 class="text-sm font-semibold">
          Known moves
          <span class="font-normal text-muted-foreground tabular-nums">
            · {{ page.props.knownMoves.length }}
          </span>
        </h3>
        <span class="hidden text-xs text-muted-foreground sm:inline">
          Drag one onto a slot to use it
        </span>
      </div>
      <PokemonMoveFilters
        v-if="page.props.knownMoves.length > 0"
        v-model:sort-order="sortOrder"
        v-model:selected-type="selectedType"
        test-id-block="pokemon-moves-card"
        :type-options="typeOptions"
      />
      <ul
        class="grid grid-cols-[repeat(auto-fill,minmax(11rem,1fr))] gap-1.5"
        data-test="pokemon-moves-card__known-move-list"
      >
        <PokemonKnownMoveTile
          v-for="move in visibleMoves"
          :key="move.id"
          :move="move"
          @drag-start="(moveId) => startDragging('known-moves', moveId)"
          @drag-end="draggedMove = null"
        />
      </ul>
    </CardContent>
    <PokemonSwapMoveDialog
      :slot-number="slotToSwap"
      @pick="pickMoveForSlot"
      @close="slotToSwap = null"
    />
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import type { ActiveMoveSlot } from '../../../composables/useActiveMoves';
import { useActiveMoves } from '../../../composables/useActiveMoves';
import { useMoveFilters } from '../../../composables/useMoveFilters';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';
import PokemonKnownMoveTile from './PokemonKnownMoveTile.vue';
import PokemonMoveFilters from './PokemonMoveFilters.vue';
import PokemonMoveSlot from './PokemonMoveSlot.vue';
import PokemonSwapMoveDialog from './PokemonSwapMoveDialog.vue';
import PokemonTeachTmDialog from './PokemonTeachTmDialog.vue';

type DraggedMove = {
  source: 'slot' | 'known-moves';
  moveId: string;
};

const page = usePage<BoxShowPageProps>();

const { slots, setActiveMove, moveToPlaceOf } = useActiveMoves();

const { sortOrder, selectedType, typeOptions, visibleMoves } = useMoveFilters(
  computed(() => page.props.knownMoves),
);

const slotToSwap = ref<number | null>(null);

const draggedMove = ref<DraggedMove | null>(null);

const startDragging = (source: DraggedMove['source'], moveId: string) => {
  draggedMove.value = { source, moveId };
};

const pickMoveForSlot = (moveId: string, slot: number) => {
  slotToSwap.value = null;

  setActiveMove(moveId, slot);
};

const dropOnSlot = (slot: ActiveMoveSlot) => {
  const droppedMove = draggedMove.value;

  draggedMove.value = null;

  if (!droppedMove || droppedMove.moveId === slot.move?.id) {
    return;
  }

  if (droppedMove.source === 'slot' && slot.move) {
    moveToPlaceOf(droppedMove.moveId, slot.move.id);

    return;
  }

  setActiveMove(droppedMove.moveId, slot.number);
};
</script>
