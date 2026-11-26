<template>
  <Dialog
    :open="slotNumber !== null"
    @update:open="(isOpen) => !isOpen && emit('close')"
  >
    <DialogContent
      class="flex max-h-[85dvh] flex-col gap-4"
      data-test="pokemon-swap-move-dialog"
    >
      <DialogHeader>
        <DialogTitle>
          {{
            currentMove
              ? `Replace ${currentMove.name}`
              : `Fill slot ${slotNumber}`
          }}
        </DialogTitle>
        <DialogDescription>
          Pick a known move for slot {{ slotNumber }}. A move already in another
          slot swaps places.
        </DialogDescription>
      </DialogHeader>
      <ul
        class="-mx-1 flex min-h-0 flex-1 scrollbar-thin flex-col gap-1 overflow-y-auto px-1 py-1"
        data-test="pokemon-swap-move-dialog__move-list"
      >
        <li
          v-for="move in candidates"
          :key="move.id"
        >
          <button
            type="button"
            class="flex w-full items-center justify-between gap-2 rounded-lg border p-2 text-left transition-colors hover:bg-muted focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none"
            :data-test="`pokemon-swap-move-dialog__move-${move.id}-button`"
            @click="slotNumber !== null && emit('pick', move.id, slotNumber)"
          >
            <span class="flex min-w-0 flex-col gap-0.5">
              <MoveNameLabel
                class="text-sm font-medium"
                :name="move.name"
                :is-z-move="move.isZMove"
                :is-dynamax-move="move.isDynamaxMove"
              />
              <PokemonMoveMeta :move="move" />
            </span>
            <Badge
              v-if="move.slot !== null"
              variant="secondary"
            >
              In slot {{ move.slot }}
            </Badge>
          </button>
        </li>
      </ul>
      <DialogFooter>
        <DialogClose as-child>
          <Button
            type="button"
            variant="ghost"
          >
            Cancel
          </Button>
        </DialogClose>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogContent,
  DialogTitle,
} from '@/shared/components/ui/dialog';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';
import PokemonMoveMeta from './PokemonMoveMeta.vue';

const props = defineProps<{
  slotNumber: number | null;
}>();

const emit = defineEmits<{
  pick: [moveId: string, slot: number];
  close: [];
}>();

const page = usePage<BoxShowPageProps>();

const currentMove = computed(
  () =>
    page.props.activeMoves.find(
      (activeMove) => activeMove.slot === props.slotNumber,
    )?.move ?? null,
);

const candidates = computed(() =>
  page.props.knownMoves.filter((move) => move.slot !== props.slotNumber),
);
</script>
