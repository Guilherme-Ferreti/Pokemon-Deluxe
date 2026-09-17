<template>
  <Card
    class="gap-4"
    data-test="pokedex-species-learnable-moves"
  >
    <CardHeader>
      <CardTitle>Moves Learned</CardTitle>
    </CardHeader>
    <CardContent class="grid grid-cols-1 gap-8 @4xl/main-content:grid-cols-2">
      <div>
        <span class="text-sm font-bold">By level up</span>
        <CardDescription class="mb-4">
          {{ speciesName }} learns the following moves at the levels specified
        </CardDescription>
        <Table class="table-content-centered table-empty-cell-placeholder">
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
                sort-by="move.name"
                :current-sort="levelUpMoves.sort.value"
                :current-dir="levelUpMoves.direction.value"
                :sort-asc-icon="IconSortAscendingLetters"
                :sort-desc-icon="IconSortDescendingLetters"
                @click="levelUpMoves.toggleSort('move.name')"
              />
              <AppTableHeadSortableButton
                column-name="Type"
                sort-by="move.type"
                :current-sort="levelUpMoves.sort.value"
                :current-dir="levelUpMoves.direction.value"
                :sort-asc-icon="IconSortAscendingLetters"
                :sort-desc-icon="IconSortDescendingLetters"
                @click="levelUpMoves.toggleSort('move.type')"
              />
              <AppTableHeadSortableButton
                column-name="Power"
                sort-by="move.power"
                :current-sort="levelUpMoves.sort.value"
                :current-dir="levelUpMoves.direction.value"
                @click="levelUpMoves.toggleSort('move.power')"
              />
              <AppTableHeadSortableButton
                column-name="Accuracy"
                sort-by="move.accuracy"
                :current-sort="levelUpMoves.sort.value"
                :current-dir="levelUpMoves.direction.value"
                @click="levelUpMoves.toggleSort('move.accuracy')"
              />
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="item in levelUpMoves.sortedItems.value"
              :key="item.move.name"
            >
              <TableCell>{{ item.levelLearn }}</TableCell>
              <TableCell>
                <MoveNameLabel
                  :name="item.move.name"
                  :is-z-move="item.move.isZMove"
                  :is-dynamax-move="item.move.isDynamaxMove"
                />
              </TableCell>
              <TableCell>
                <ElementalTypeBadges :types="[item.move.type]" />
              </TableCell>
              <TableCell>
                <MovePowerLabel
                  :power="item.move.power"
                  :category="item.move.category"
                />
              </TableCell>
              <TableCell>{{ item.move.accuracy }}%</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <div>
        <span class="text-sm font-bold">By TM</span>
        <CardDescription class="mb-4">
          {{ speciesName }} is compatible with these Technical Machines
        </CardDescription>
        <Table class="table-content-centered table-empty-cell-placeholder">
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
                sort-by="move.name"
                :current-sort="tmMoves.sort.value"
                :current-dir="tmMoves.direction.value"
                :sort-asc-icon="IconSortAscendingLetters"
                :sort-desc-icon="IconSortDescendingLetters"
                @click="tmMoves.toggleSort('move.name')"
              />
              <AppTableHeadSortableButton
                column-name="Type"
                sort-by="move.type"
                :current-sort="tmMoves.sort.value"
                :current-dir="tmMoves.direction.value"
                :sort-asc-icon="IconSortAscendingLetters"
                :sort-desc-icon="IconSortDescendingLetters"
                @click="tmMoves.toggleSort('move.type')"
              />
              <AppTableHeadSortableButton
                column-name="Power"
                sort-by="move.power"
                :current-sort="tmMoves.sort.value"
                :current-dir="tmMoves.direction.value"
                @click="tmMoves.toggleSort('move.power')"
              />
              <AppTableHeadSortableButton
                column-name="Accuracy"
                sort-by="move.accuracy"
                :current-sort="tmMoves.sort.value"
                :current-dir="tmMoves.direction.value"
                @click="tmMoves.toggleSort('move.accuracy')"
              />
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="item in tmMoves.sortedItems.value"
              :key="item.move.name"
            >
              <TableCell>{{ item.tmCode }}</TableCell>
              <TableCell>
                <MoveNameLabel
                  :name="item.move.name"
                  :is-z-move="item.move.isZMove"
                  :is-dynamax-move="item.move.isDynamaxMove"
                />
              </TableCell>
              <TableCell>
                <ElementalTypeBadges :types="[item.move.type]" />
              </TableCell>
              <TableCell>
                <MovePowerLabel
                  :power="item.move.power"
                  :category="item.move.category"
                />
              </TableCell>
              <TableCell>{{ item.move.accuracy }}%</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import {
  IconSortAscendingLetters,
  IconSortDescendingLetters,
} from '@tabler/icons-vue';
import AppTableHeadSortableButton from '@/components/AppTableHeadSortableButton.vue';
import ElementalTypeBadges from '@/components/ElementalTypeBadges.vue';
import MoveNameLabel from '@/components/MoveNameLabel.vue';
import MovePowerLabel from '@/components/MovePowerLabel.vue';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { useSortable } from '@/composables/useSortable.js';
import type { DexPokedexShowPageProps } from '@/pages/Dex/Pokedex/Show.vue';

const props = defineProps<{
  speciesName: string;
  learnableMoves: DexPokedexShowPageProps['learnableMoves'];
}>();

const levelUpMoves = useSortable<
  DexPokedexShowPageProps['learnableMoves']['levelUp'][number]
>(props.learnableMoves.levelUp, 'levelLearn');

const tmMoves = useSortable<
  DexPokedexShowPageProps['learnableMoves']['tm'][number]
>(props.learnableMoves.tm, 'tmCode');
</script>
