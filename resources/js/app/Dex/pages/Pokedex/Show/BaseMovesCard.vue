<template>
  <AppInfoCard
    title="Base Moves"
    data-test="base-moves-card"
    :description="`The moves ${page.props.species.name} knows when caught`"
  >
    <Table class="table-content-centered table-empty-cell-placeholder">
      <TableHeader>
        <TableRow>
          <AppTableHeadSortableButton
            class="w-55"
            column-name="Move"
            sort-by="name"
            :current-sort="baseMoves.sort.value"
            :current-dir="baseMoves.direction.value"
            :sort-asc-icon="IconSortAscendingLetters"
            :sort-desc-icon="IconSortDescendingLetters"
            @click="baseMoves.toggleSort('name')"
          />
          <AppTableHeadSortableButton
            column-name="Type"
            sort-by="type"
            :current-sort="baseMoves.sort.value"
            :current-dir="baseMoves.direction.value"
            :sort-asc-icon="IconSortAscendingLetters"
            :sort-desc-icon="IconSortDescendingLetters"
            @click="baseMoves.toggleSort('type')"
          />
          <AppTableHeadSortableButton
            column-name="Power"
            sort-by="power"
            data-test="base-moves-card__head-power"
            :current-sort="baseMoves.sort.value"
            :current-dir="baseMoves.direction.value"
            @click="baseMoves.toggleSort('power')"
          />
          <AppTableHeadSortableButton
            column-name="Accuracy"
            sort-by="accuracy"
            :current-sort="baseMoves.sort.value"
            :current-dir="baseMoves.direction.value"
            @click="baseMoves.toggleSort('accuracy')"
          />
          <AppTableHeadSortableButton
            column-name="Ailment"
            sort-by="ailment"
            :current-sort="baseMoves.sort.value"
            :current-dir="baseMoves.direction.value"
            :sort-asc-icon="IconSortAscendingLetters"
            :sort-desc-icon="IconSortDescendingLetters"
            @click="baseMoves.toggleSort('ailment')"
          />
          <AppTableHeadSortableButton
            column-name="Ailment chance"
            sort-by="ailmentChance"
            :current-sort="baseMoves.sort.value"
            :current-dir="baseMoves.direction.value"
            @click="baseMoves.toggleSort('ailmentChance')"
          />
          <AppTableHeadSortableButton
            column-name="Flinch chance"
            sort-by="flinchChance"
            :current-sort="baseMoves.sort.value"
            :current-dir="baseMoves.direction.value"
            @click="baseMoves.toggleSort('flinchChance')"
          />
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="(move, index) in baseMoves.sortedItems.value"
          :key="move.name"
        >
          <TableCell :data-test="`base-moves-card__row-${index + 1}-name`">
            <MoveNameLabel
              :name="move.name"
              :is-z-move="move.isZMove"
              :is-dynamax-move="move.isDynamaxMove"
            />
          </TableCell>
          <TableCell :data-test="`base-moves-card__row-${index + 1}-type`">
            <PokemonTypeBadges :types="[move.type]" />
          </TableCell>
          <TableCell :data-test="`base-moves-card__row-${index + 1}-power`">
            <MovePowerLabel
              :power="move.power"
              :category="move.category"
            />
          </TableCell>
          <TableCell :data-test="`base-moves-card__row-${index + 1}-accuracy`">
            {{ move.accuracy }}%
          </TableCell>
          <TableCell :data-test="`base-moves-card__row-${index + 1}-ailment`">
            {{ move.ailment ? $page.props.labels.ailment[move.ailment] : '' }}
          </TableCell>
          <TableCell
            :data-test="`base-moves-card__row-${index + 1}-ailment-chance`"
          >
            {{ move.ailmentChance ? `${move.ailmentChance}%` : '' }}
          </TableCell>
          <TableCell
            :data-test="`base-moves-card__row-${index + 1}-flinch-chance`"
          >
            {{ move.flinchChance ? `${move.flinchChance}%` : '' }}
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </AppInfoCard>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
  IconSortAscendingLetters,
  IconSortDescendingLetters,
} from '@tabler/icons-vue';
import AppInfoCard from '@/shared/components/content/AppInfoCard.vue';
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
  BaseMove,
  PokedexShowPageProps,
} from '../../../types/page-props/PokedexShowPageProps';

const page = usePage<PokedexShowPageProps>();

const baseMoves = useSortable<BaseMove>(
  page.props.baseMoves.map((move) => ({
    ...move,
    power: move.category === MoveCategory.OHKO ? 999 : move.power,
  })),
  'name',
);
</script>
