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
              <TableCell>{{ item.levelLearn }}</TableCell>
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
      </div>
      <div>
        <span class="text-sm font-bold">By TM</span>
        <CardDescription class="mb-4">
          {{ speciesName }} is compatible with these Technical Machines
        </CardDescription>
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
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import {
  IconSortAscendingLetters,
  IconSortDescendingLetters,
} from '@tabler/icons-vue';
import type {
  LevelUpLearnableMove,
  PokedexShowPageProps,
  TMLearnableMove,
} from '@/app/Dex/types/page-props/PokedexShowPageProps';
import AppTableHeadSortableButton from '@/shared/components/AppTableHeadSortableButton.vue';
import ElementalTypeBadges from '@/shared/components/ElementalTypeBadges.vue';
import MoveNameLabel from '@/shared/components/MoveNameLabel.vue';
import MovePowerLabel from '@/shared/components/MovePowerLabel.vue';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHeader,
  TableRow,
} from '@/shared/components/ui/table';
import { useSortable } from '@/shared/composables/useSortable.js';
import { MoveCategory } from '@/shared/types/enums';
import type { Species } from '@/shared/types/models';

const props = defineProps<{
  speciesName: Species['name'];
  learnableMoves: PokedexShowPageProps['learnableMoves'];
}>();

const levelUpMoves = useSortable<LevelUpLearnableMove>(
  setMaxPowerForOHKOMoves<LevelUpLearnableMove>(props.learnableMoves.levelUp),
  'levelLearn',
);

const tmMoves = useSortable<TMLearnableMove>(
  setMaxPowerForOHKOMoves<TMLearnableMove>(props.learnableMoves.tm),
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
