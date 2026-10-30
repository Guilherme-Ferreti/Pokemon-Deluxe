<template>
  <Table
    class="table-content-centered table-empty-cell-placeholder"
    data-test="pokedex-species-learnable-moves__tm"
  >
    <TableHeader>
      <TableRow>
        <AppTableHeadSortableButton
          column-name="TM"
          sort-by="tmCode"
          :current-sort="tmMoves.sort.value"
          :current-dir="tmMoves.direction.value"
          @click="tmMoves.toggleSort('tmCode')"
        />
        <AppTableHeadSortableButton
          column-name="Move"
          sort-by="name"
          :current-sort="tmMoves.sort.value"
          :current-dir="tmMoves.direction.value"
          :sort-asc-icon="IconSortAscendingLetters"
          :sort-desc-icon="IconSortDescendingLetters"
          @click="tmMoves.toggleSort('name')"
        />
        <AppTableHeadSortableButton
          column-name="Type"
          sort-by="type"
          :current-sort="tmMoves.sort.value"
          :current-dir="tmMoves.direction.value"
          :sort-asc-icon="IconSortAscendingLetters"
          :sort-desc-icon="IconSortDescendingLetters"
          @click="tmMoves.toggleSort('type')"
        />
        <AppTableHeadSortableButton
          column-name="Power"
          sort-by="power"
          :current-sort="tmMoves.sort.value"
          :current-dir="tmMoves.direction.value"
          @click="tmMoves.toggleSort('power')"
        />
        <AppTableHeadSortableButton
          column-name="Accuracy"
          sort-by="accuracy"
          :current-sort="tmMoves.sort.value"
          :current-dir="tmMoves.direction.value"
          @click="tmMoves.toggleSort('accuracy')"
        />
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow
        v-for="item in tmMoves.sortedItems.value"
        :key="item.name"
      >
        <TableCell>{{ item.tmCode }}</TableCell>
        <TableCell>
          <MoveNameLabel
            :name="item.name"
            :is-z-move="item.isZMove"
            :is-dynamax-move="item.isDynamaxMove"
          />
        </TableCell>
        <TableCell>
          <ElementalTypeBadges :types="[item.type]" />
        </TableCell>
        <TableCell>
          <MovePowerLabel
            :power="item.power"
            :category="item.category"
          />
        </TableCell>
        <TableCell>{{ item.accuracy }}%</TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
  IconSortAscendingLetters,
  IconSortDescendingLetters,
} from '@tabler/icons-vue';
import type {
  LevelUpLearnableMove,
  PokedexShowPageProps,
  TMLearnableMove,
} from '@/app/Dex/types/page-props/PokedexShowPageProps';
import ElementalTypeBadges from '@/shared/components/ElementalTypeBadges.vue';
import AppTableHeadSortableButton from '@/shared/components/list/AppTableHeadSortableButton.vue';
import MoveNameLabel from '@/shared/components/MoveNameLabel.vue';
import MovePowerLabel from '@/shared/components/MovePowerLabel.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from '@/shared/components/ui/table';
import { useSortable } from '@/shared/composables/useSortable.js';
import { MoveCategory } from '@/shared/types/enums';

const page = usePage<PokedexShowPageProps>();

const tmMoves = useSortable<TMLearnableMove>(
  setMaxPowerForOHKOMoves<TMLearnableMove>(page.props.learnableMoves.tm),
  'tmCode',
);

function setMaxPowerForOHKOMoves<
  T extends LevelUpLearnableMove | TMLearnableMove,
>(moves: T[]) {
  return moves.map((move) => {
    return {
      ...move,
      power: move.category === MoveCategory.OHKO ? 999 : move.power,
    };
  });
}
</script>
