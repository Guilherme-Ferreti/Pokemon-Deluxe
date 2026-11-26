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
        <li
          v-for="slot in slots"
          :key="slot.number"
          class="flex items-center gap-2 rounded-lg border p-2 transition-colors"
          :class="[
            slot.move ? 'bg-card' : 'border-dashed',
            dragOverSlot === slot.number &&
              'border-primary ring-1 ring-primary',
          ]"
          :draggable="slot.move !== null && !isSaving"
          :data-test="`pokemon-moves-card__slot-${slot.number}-item`"
          @dragstart="startDraggingSlot(slot.number)"
          @dragover.prevent="dragOverSlot = slot.number"
          @dragleave="dragOverSlot = null"
          @drop.prevent="dropOnSlot(slot.number)"
          @dragend="stopDragging"
        >
          <IconGripVertical
            v-if="slot.move"
            class="hidden size-4 shrink-0 cursor-grab text-muted-foreground sm:block"
            aria-hidden="true"
          />
          <span
            class="w-4 shrink-0 text-center text-sm font-semibold text-muted-foreground tabular-nums"
          >
            {{ slot.number }}
          </span>
          <template v-if="slot.move">
            <span class="flex min-w-0 flex-1 flex-col gap-0.5">
              <MoveNameLabel
                class="truncate text-sm font-medium"
                :name="slot.move.name"
                :is-z-move="slot.move.isZMove"
                :is-dynamax-move="slot.move.isDynamaxMove"
              />
              <PokemonMoveMeta :move="slot.move" />
            </span>
            <span class="flex shrink-0 items-center gap-0.5">
              <Button
                type="button"
                variant="ghost"
                size="icon-sm"
                :aria-label="`Move ${slot.move.name} up`"
                :data-test="`pokemon-moves-card__slot-${slot.number}-up-button`"
                :disabled="isSaving || slot.position === 0"
                @click="moveActiveMove(slot.position, -1)"
              >
                <IconChevronUp />
              </Button>
              <Button
                type="button"
                variant="ghost"
                size="icon-sm"
                :aria-label="`Move ${slot.move.name} down`"
                :data-test="`pokemon-moves-card__slot-${slot.number}-down-button`"
                :disabled="
                  isSaving ||
                  slot.position === page.props.activeMoves.length - 1
                "
                @click="moveActiveMove(slot.position, 1)"
              >
                <IconChevronDown />
              </Button>
            </span>
          </template>
          <span
            v-else
            class="flex-1 text-sm text-muted-foreground"
          >
            Empty slot
          </span>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            :data-test="`pokemon-moves-card__slot-${slot.number}-swap-button`"
            :disabled="isSaving"
            @click="slotToSwap = slot.number"
          >
            {{ slot.move ? 'Swap' : 'Choose' }}
          </Button>
        </li>
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
      <ul
        class="grid grid-cols-[repeat(auto-fill,minmax(11rem,1fr))] gap-1.5"
        data-test="pokemon-moves-card__known-move-list"
      >
        <li
          v-for="move in page.props.knownMoves"
          :key="move.id"
          class="flex min-w-0 flex-col gap-0.5 rounded-lg border border-dashed p-2"
          :class="move.slot === null ? 'cursor-grab' : 'opacity-50'"
          :draggable="move.slot === null && !isSaving"
          @dragstart="startDraggingKnownMove(move.id)"
          @dragend="stopDragging"
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
      </ul>
    </CardContent>
    <PokemonSwapMoveDialog
      :slot-number="slotToSwap"
      @pick="setActiveMove"
      @close="slotToSwap = null"
    />
  </Card>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import {
  IconChevronDown,
  IconChevronUp,
  IconGripVertical,
} from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import { route } from '@/shared/types/helpers/route';
import type { PokemonShowPageProps } from '../../../types/page-props/PokemonShowPageProps';
import PokemonMoveMeta from './PokemonMoveMeta.vue';
import PokemonSwapMoveDialog from './PokemonSwapMoveDialog.vue';
import PokemonTeachTmDialog from './PokemonTeachTmDialog.vue';

const MAX_ACTIVE_MOVES = 4;

const page = usePage<PokemonShowPageProps>();

const isSaving = ref(false);

const slotToSwap = ref<number | null>(null);

const draggedSlot = ref<number | null>(null);

const draggedKnownMoveId = ref<string | null>(null);

const dragOverSlot = ref<number | null>(null);

const slots = computed(() =>
  Array.from({ length: MAX_ACTIVE_MOVES }, (_, index) => {
    const number = index + 1;
    const position = page.props.activeMoves.findIndex(
      (activeMove) => activeMove.slot === number,
    );

    return {
      number,
      position,
      move: position === -1 ? null : page.props.activeMoves[position].move,
    };
  }),
);

const post = (url: string, data: Parameters<typeof router.post>[1]) => {
  router.post(url, data, {
    preserveScroll: true,
    onStart: () => (isSaving.value = true),
    onFinish: () => (isSaving.value = false),
  });
};

const setActiveMove = (moveId: string, slot: number) => {
  slotToSwap.value = null;

  post(
    route('pokemon.web.set-active-move', { pokemon: page.props.pokemon.id }),
    { move_id: moveId, slot },
  );
};

const reorderActiveMoves = (moveIds: string[]) => {
  post(
    route('pokemon.web.reorder-active-moves', {
      pokemon: page.props.pokemon.id,
    }),
    { move_ids: moveIds },
  );
};

const moveActiveMove = (position: number, offset: number) => {
  const moveIds = page.props.activeMoves.map(
    (activeMove) => activeMove.move.id,
  );
  const [movedId] = moveIds.splice(position, 1);

  moveIds.splice(position + offset, 0, movedId);

  reorderActiveMoves(moveIds);
};

const startDraggingSlot = (number: number) => {
  draggedSlot.value = number;
};

const startDraggingKnownMove = (moveId: string) => {
  draggedKnownMoveId.value = moveId;
};

const stopDragging = () => {
  draggedSlot.value = null;
  draggedKnownMoveId.value = null;
  dragOverSlot.value = null;
};

const dropOnSlot = (number: number) => {
  const fromSlot = draggedSlot.value;
  const knownMoveId = draggedKnownMoveId.value;

  stopDragging();

  if (knownMoveId !== null) {
    setActiveMove(knownMoveId, number);

    return;
  }

  const from = slots.value.find((slot) => slot.number === fromSlot);
  const to = slots.value.find((slot) => slot.number === number);

  if (!from?.move || !to?.move || from.number === to.number) {
    return;
  }

  moveActiveMove(from.position, to.position - from.position);
};
</script>
