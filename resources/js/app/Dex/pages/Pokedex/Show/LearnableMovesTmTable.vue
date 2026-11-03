<template>
  <Table
    class="table-content-centered table-empty-cell-placeholder"
    data-test="learnable-moves-tm-table"
  >
    <TableHeader>
      <TableRow>
        <AppTableHeadSortableButton
          column-name="TM"
          sort-by="tmCode"
          data-test="learnable-moves-tm-table__head-tm-code"
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
        v-for="(item, index) in tmMoves.sortedItems.value"
        :key="item.name"
      >
        <TableCell
          :data-test="`learnable-moves-tm-table__row-${index + 1}-tm-code`"
        >
          {{ item.tmCode }}
        </TableCell>
        <TableCell
          :data-test="`learnable-moves-tm-table__row-${index + 1}-name`"
        >
          <MoveNameLabel
            :name="item.name"
            :is-z-move="item.isZMove"
            :is-dynamax-move="item.isDynamaxMove"
          />
        </TableCell>
        <TableCell
          :data-test="`learnable-moves-tm-table__row-${index + 1}-type`"
        >
          <ElementalTypeBadges :types="[item.type]" />
        </TableCell>
        <TableCell
          :data-test="`learnable-moves-tm-table__row-${index + 1}-power`"
        >
          <MovePowerLabel
            :power="item.power"
            :category="item.category"
          />
        </TableCell>
        <TableCell
          :data-test="`learnable-moves-tm-table__row-${index + 1}-accuracy`"
        >
          {{ item.accuracy }}%
        </TableCell>
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
import ElementalTypeBadges from '@/shared/components/game/ElementalTypeBadges.vue';
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
import MovePowerLabel from '@/shared/components/game/MovePowerLabel.vue';
import AppTableHeadSortableButton from '@/shared/components/list/AppTableHeadSortableButton.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from '@/shared/components/ui/table';
import { useSortable } from '@/shared/composables/useSortable.js';
import { MoveCategory } from '@/shared/types/enums';
import type {
  LevelUpLearnableMove,
  PokedexShowPageProps,
  TMLearnableMove,
} from '../../../types/page-props/PokedexShowPageProps';

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
