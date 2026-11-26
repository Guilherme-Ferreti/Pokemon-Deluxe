<template>
  <li
    class="flex items-center gap-2 rounded-lg border p-2 transition-colors"
    :class="[
      moveSlot.move ? 'bg-card' : 'border-dashed',
      isDragOver && 'border-primary ring-1 ring-primary',
    ]"
    :draggable="moveSlot.move !== null"
    :data-test="`pokemon-move-${moveSlot.number}-slot`"
    @dragstart="moveSlot.move && emit('drag-start', moveSlot.move.id)"
    @dragover.prevent="isDragOver = true"
    @dragleave="isDragOver = false"
    @drop.prevent="dropOnSlot"
    @dragend="emit('drag-end')"
  >
    <IconGripVertical
      v-if="moveSlot.move"
      class="hidden size-4 shrink-0 cursor-grab text-muted-foreground sm:block"
      aria-hidden="true"
    />
    <span
      class="w-4 shrink-0 text-center text-sm font-semibold text-muted-foreground tabular-nums"
    >
      {{ moveSlot.number }}
    </span>
    <template v-if="moveSlot.move">
      <span class="flex min-w-0 flex-1 flex-col gap-1">
        <MoveNameLabel
          class="truncate text-sm font-medium"
          :name="moveSlot.move.name"
          :is-z-move="moveSlot.move.isZMove"
          :is-dynamax-move="moveSlot.move.isDynamaxMove"
        />
        <PokemonMoveMeta :move="moveSlot.move" />
      </span>
      <span class="flex shrink-0 items-center gap-0.5">
        <Button
          type="button"
          variant="ghost"
          size="icon-sm"
          :aria-label="`Move ${moveSlot.move.name} up`"
          :data-test="`pokemon-move-${moveSlot.number}-slot__up-button`"
          :disabled="isFirstActiveMove(moveSlot.move.id)"
          @click="moveUp(moveSlot.move.id)"
        >
          <IconChevronUp />
        </Button>
        <Button
          type="button"
          variant="ghost"
          size="icon-sm"
          :aria-label="`Move ${moveSlot.move.name} down`"
          :data-test="`pokemon-move-${moveSlot.number}-slot__down-button`"
          :disabled="isLastActiveMove(moveSlot.move.id)"
          @click="moveDown(moveSlot.move.id)"
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
      :data-test="`pokemon-move-${moveSlot.number}-slot__swap-button`"
      @click="emit('swap')"
    >
      {{ moveSlot.move ? 'Swap' : 'Choose' }}
    </Button>
  </li>
</template>

<script setup lang="ts">
import {
  IconChevronDown,
  IconChevronUp,
  IconGripVertical,
} from '@tabler/icons-vue';
import { ref } from 'vue';
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
import { Button } from '@/shared/components/ui/button';
import type { ActiveMoveSlot } from '../../../composables/useActiveMoves';
import { useActiveMoves } from '../../../composables/useActiveMoves';
import PokemonMoveMeta from './PokemonMoveMeta.vue';

defineProps<{
  moveSlot: ActiveMoveSlot;
}>();

const emit = defineEmits<{
  swap: [];
  'drag-start': [moveId: string];
  'drag-end': [];
  drop: [];
}>();

const { moveUp, moveDown, isFirstActiveMove, isLastActiveMove } =
  useActiveMoves();

const isDragOver = ref(false);

const dropOnSlot = () => {
  isDragOver.value = false;

  emit('drop');
};
</script>
