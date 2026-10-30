<template>
  <Table
    class="table-content-centered table-empty-cell-placeholder"
    data-test="pokedex-species-learnable-moves__level-up"
  >
    <TableHeader>
      <TableRow>
        <AppTableHeadSortableButton
          column-name="Level"
          sort-by="levelLearn"
          :current-sort="levelUpMoves.sort.value"
          :current-dir="levelUpMoves.direction.value"
          @click="levelUpMoves.toggleSort('levelLearn')"
        />
        <AppTableHeadSortableButton
          column-name="Move"
          sort-by="name"
          :current-sort="levelUpMoves.sort.value"
          :current-dir="levelUpMoves.direction.value"
          :sort-asc-icon="IconSortAscendingLetters"
          :sort-desc-icon="IconSortDescendingLetters"
          @click="levelUpMoves.toggleSort('name')"
        />
        <AppTableHeadSortableButton
          column-name="Type"
          sort-by="type"
          :current-sort="levelUpMoves.sort.value"
          :current-dir="levelUpMoves.direction.value"
          :sort-asc-icon="IconSortAscendingLetters"
          :sort-desc-icon="IconSortDescendingLetters"
          @click="levelUpMoves.toggleSort('type')"
        />
        <AppTableHeadSortableButton
          column-name="Power"
          sort-by="power"
          :current-sort="levelUpMoves.sort.value"
          :current-dir="levelUpMoves.direction.value"
          @click="levelUpMoves.toggleSort('power')"
        />
        <AppTableHeadSortableButton
          column-name="Accuracy"
          sort-by="accuracy"
          :current-sort="levelUpMoves.sort.value"
          :current-dir="levelUpMoves.direction.value"
          @click="levelUpMoves.toggleSort('accuracy')"
        />
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow
        v-for="item in levelUpMoves.sortedItems.value"
        :key="item.name"
      >
        <TableCell date-test="cell-levelLearn">
          {{ item.levelLearn }}
        </TableCell>
        <TableCell date-test="cell-name">
          <MoveNameLabel
            :name="item.name"
            :is-z-move="item.isZMove"
            :is-dynamax-move="item.isDynamaxMove"
          />
        </TableCell>
        <TableCell date-test="cell-type">
          <ElementalTypeBadges :types="[item.type]" />
        </TableCell>
        <TableCell date-test="cell-power">
          <MovePowerLabel
            :power="item.power"
            :category="item.category"
          />
        </TableCell>
        <TableCell date-test="cell-accuracy"> {{ item.accuracy }}% </TableCell>
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

const levelUpMoves = useSortable<LevelUpLearnableMove>(
  setMaxPowerForOHKOMoves<LevelUpLearnableMove>(
    page.props.learnableMoves.levelUp,
  ),
  'levelLearn',
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
