<template>
  <li
    class="relative flex flex-col items-center gap-2 rounded-lg border bg-card p-3 pt-8 text-center transition-colors duration-200 hover:border-primary/50 motion-reduce:transition-none"
    :class="[
      isDragOver && 'border-primary ring-1 ring-primary',
      isDragged && 'opacity-50',
    ]"
    :draggable="canReorder"
    :data-test="`party-${position}-slot`"
    @dragstart="startDragging"
    @dragend="stopDragging"
    @dragover.prevent="canReorder && (isDragOver = true)"
    @dragleave="isDragOver = false"
    @drop.prevent="dropOnSlot"
  >
    <Badge
      variant="secondary"
      class="absolute top-2 left-2 tabular-nums"
      :aria-label="`Position ${position}`"
    >
      {{ position }}
    </Badge>
    <Badge
      v-if="position === 1"
      class="absolute top-2 right-2"
      :data-test="`party-${position}-slot__leads-indicator`"
    >
      Leads
    </Badge>
    <IconGripVertical
      v-else-if="canReorder"
      class="absolute top-2.5 right-2 hidden size-4 cursor-grab text-muted-foreground @md/party:block"
      aria-hidden="true"
    />
    <SpeciesPortrait
      class="size-12"
      :url="member.portrait"
      :name="member.name"
    />
    <Link
      class="text-sm font-medium text-foreground after:absolute after:inset-0 after:rounded-lg focus-visible:outline-none focus-visible:after:ring-3 focus-visible:after:ring-ring/50"
      :href="route('pokemon.web.box.show', { pokemon: member.id })"
      :data-test="`party-${position}-slot__link`"
    >
      <PokemonDisplayName
        :name="member.name"
        :gender="member.gender"
      />
    </Link>
    <span class="text-xs text-muted-foreground tabular-nums">
      Lv {{ member.level
      }}<template v-if="member.boost > 0"> · +{{ member.boost }}</template>
    </span>
    <PokemonTypeBadges
      class="mt-auto justify-center"
      :types="member.types"
    />
    <span class="relative z-10 mt-auto flex gap-1">
      <Button
        type="button"
        variant="ghost"
        size="icon-sm"
        :aria-label="`Move ${member.name} earlier`"
        :data-test="`party-${position}-slot__move-earlier-button`"
        :disabled="!canReorder || position === 1"
        @click="emit('move-earlier')"
      >
        <IconArrowUp class="@md/party:hidden" />
        <IconArrowLeft class="hidden @md/party:block" />
      </Button>
      <Button
        type="button"
        variant="ghost"
        size="icon-sm"
        :aria-label="`Move ${member.name} later`"
        :data-test="`party-${position}-slot__move-later-button`"
        :disabled="!canReorder || isLast"
        @click="emit('move-later')"
      >
        <IconArrowDown class="@md/party:hidden" />
        <IconArrowRight class="hidden @md/party:block" />
      </Button>
    </span>
  </li>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
  IconArrowDown,
  IconArrowLeft,
  IconArrowRight,
  IconArrowUp,
  IconGripVertical,
} from '@tabler/icons-vue';
import { ref } from 'vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { route } from '@/shared/types/helpers/route';
import type { PartyMemberSlot } from '../../types/page-props/PartyPageProps';

const props = defineProps<{
  member: PartyMemberSlot;
  position: number;
  isLast: boolean;
  canReorder: boolean;
}>();

const emit = defineEmits<{
  'move-earlier': [];
  'move-later': [];
  'drag-start': [];
  'drag-end': [];
  drop: [];
}>();

const isDragOver = ref(false);

const isDragged = ref(false);

const startDragging = (event: DragEvent) => {
  if (!props.canReorder) {
    event.preventDefault();

    return;
  }

  event.dataTransfer?.setData('text/plain', props.member.id);

  isDragged.value = true;

  emit('drag-start');
};

const stopDragging = () => {
  isDragged.value = false;

  emit('drag-end');
};

const dropOnSlot = () => {
  isDragOver.value = false;

  emit('drop');
};
</script>
