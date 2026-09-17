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
          {{ speciesName }} learns the following moves at the levels specified.
        </CardDescription>
        <Table class="table-content-centered table-empty-cell-placeholder">
          <TableHeader>
            <TableRow>
              <TableHead @click="levelUpMoves.toggleSort('levelLearn')">
                Level
              </TableHead>
              <TableHead @click="levelUpMoves.toggleSort('move.name')">
                Move
              </TableHead>
              <TableHead @click="levelUpMoves.toggleSort('move.type')">
                Type
              </TableHead>
              <TableHead @click="levelUpMoves.toggleSort('move.power')">
                Power
              </TableHead>
              <TableHead @click="levelUpMoves.toggleSort('move.accuracy')">
                Accuracy
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="item in levelUpMoves.sortedItems.value"
              :key="item.move.name"
            >
              <TableCell>{{ item.levelLearn }}</TableCell>
              <TableCell>
                {{ item.move.name }}
                <sup
                  v-if="item.move.isZmove || item.move.isDynamax"
                  class="font-extrabold"
                >
                  {{ item.move.isZmove ? 'Z' : 'D' }}
                </sup>
              </TableCell>
              <TableCell>
                <ElementalTypeBadges :types="[item.move.type]" />
              </TableCell>
              <TableCell>
                {{
                  item.move.category === MoveCategory.OHKO
                    ? 'One-hit KO'
                    : item.move.power
                }}
              </TableCell>
              <TableCell>{{ item.move.accuracy }}%</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      <div>
        <span class="text-sm font-bold">By TM</span>
        <CardDescription class="mb-4">
          {{ speciesName }} is compatible with these Technical Machines.
        </CardDescription>
        <Table class="table-content-centered table-empty-cell-placeholder">
          <TableHeader>
            <TableRow>
              <TableHead @click="tmMoves.toggleSort('tmCode')">TM</TableHead>
              <TableHead @click="tmMoves.toggleSort('move.name')"
                >Move</TableHead
              >
              <TableHead @click="tmMoves.toggleSort('move.type')"
                >Type</TableHead
              >
              <TableHead @click="tmMoves.toggleSort('move.power')"
                >Power</TableHead
              >
              <TableHead @click="tmMoves.toggleSort('move.accuracy')">
                Accuracy
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="item in tmMoves.sortedItems.value"
              :key="item.move.name"
            >
              <TableCell>{{ item.tmCode }}</TableCell>
              <TableCell>{{ item.move.name }}</TableCell>
              <TableCell>
                <ElementalTypeBadges :types="[item.move.type]" />
              </TableCell>
              <TableCell>
                {{
                  item.move.category === MoveCategory.OHKO
                    ? 'One-hit KO'
                    : item.move.power
                }}
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
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { useSortable } from '@/composables/useSortable.js';
import type { DexPokedexShowPageProps } from '@/pages/Dex/Pokedex/Show.vue';
import { MoveCategory } from '@/types';
import ElementalTypeBadges from './ElementalTypeBadges.vue';

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
