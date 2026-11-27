<template>
  <Card
    class="@container/party gap-3"
    data-test="party-list"
  >
    <CardHeader class="flex items-center justify-between gap-2">
      <CardTitle>Party</CardTitle>
      <span
        class="text-xs text-muted-foreground tabular-nums"
        data-test="party-list__count-indicator"
      >
        {{ page.props.party.length }} / {{ page.props.maxPartySize }}
      </span>
    </CardHeader>
    <CardContent>
      <ol
        class="grid grid-cols-1 gap-3 @md/party:grid-cols-3 @4xl/party:grid-cols-6"
        aria-label="Party in sending-out order"
      >
        <PartySlot
          v-for="(member, index) in page.props.party"
          :key="member.id"
          :member="member"
          :position="index + 1"
          :is-last="index === page.props.party.length - 1"
          :can-reorder="canReorder"
          @move-earlier="movePokemon(index, index - 1)"
          @move-later="movePokemon(index, index + 1)"
          @drag-start="draggedIndex = index"
          @drag-end="draggedIndex = null"
          @drop="dropOn(index)"
        />
        <li
          v-for="emptySlot in emptySlotCount"
          :key="`empty-${emptySlot}`"
        >
          <Link
            class="flex h-full min-h-40 flex-col items-center justify-center gap-2 rounded-lg border border-dashed bg-muted/30 p-3 text-sm text-muted-foreground transition-colors duration-200 hover:border-primary/50 motion-reduce:transition-none"
            :href="route('pokemon.web.box.index')"
            :data-test="`party-list__empty-slot-${emptySlot}-link`"
          >
            <IconPlus class="size-5" />
            Add from your Box
          </Link>
        </li>
      </ol>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { IconPlus } from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import { route } from '@/shared/types/helpers/route';
import { usePartyOrder } from '../../composables/usePartyOrder';
import type { PartyPageProps } from '../../types/page-props/PartyPageProps';
import PartySlot from './PartySlot.vue';

const page = usePage<PartyPageProps>();

const { canReorder, movePokemon } = usePartyOrder();

const draggedIndex = ref<number | null>(null);

const emptySlotCount = computed(() =>
  Math.max(0, page.props.maxPartySize - page.props.party.length),
);

const dropOn = (index: number) => {
  if (draggedIndex.value === null) {
    return;
  }

  movePokemon(draggedIndex.value, index);

  draggedIndex.value = null;
};
</script>
