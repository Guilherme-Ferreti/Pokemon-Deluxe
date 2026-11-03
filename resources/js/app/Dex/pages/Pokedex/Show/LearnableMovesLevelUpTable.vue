<template>
  <Table
    class="table-content-centered table-empty-cell-placeholder"
    data-test="learnable-moves-level-up-table"
  >
    <TableHeader>
      <TableRow>
        <AppTableHeadSortableButton
          column-name="Level"
          sort-by="levelLearn"
          data-test="learnable-moves-level-up-table__head-level-learn"
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
          data-test="learnable-moves-level-up-table__head-power"
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
        v-for="(item, index) in levelUpMoves.sortedItems.value"
        :key="item.name"
      >
        <TableCell
          :data-test="`learnable-moves-level-up-table__row-${index + 1}-level-learn`"
        >
          {{ item.levelLearn }}
        </TableCell>
        <TableCell
          :data-test="`learnable-moves-level-up-table__row-${index + 1}-name`"
        >
          <MoveNameLabel
            :name="item.name"
            :is-z-move="item.isZMove"
            :is-dynamax-move="item.isDynamaxMove"
          />
        </TableCell>
        <TableCell
          :data-test="`learnable-moves-level-up-table__row-${index + 1}-type`"
        >
          <PokemonTypeBadges :types="[item.type]" />
        </TableCell>
        <TableCell
          :data-test="`learnable-moves-level-up-table__row-${index + 1}-power`"
        >
          <MovePowerLabel
            :power="item.power"
            :category="item.category"
          />
        </TableCell>
        <TableCell
          :data-test="`learnable-moves-level-up-table__row-${index + 1}-accuracy`"
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
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
import MovePowerLabel from '@/shared/components/game/MovePowerLabel.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
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
