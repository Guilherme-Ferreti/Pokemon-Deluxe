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
              <TableHead>Level</TableHead>
              <TableHead>Move</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Power</TableHead>
              <TableHead>Accuracy</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="learnableMove in learnableMoves.levelUp"
              :key="learnableMove.move.name"
            >
              <TableCell>{{ learnableMove.levelLearn }}</TableCell>
              <TableCell>
                {{ learnableMove.move.name }}
                <sup
                  v-if="
                    learnableMove.move.isZmove || learnableMove.move.isDynamax
                  "
                  class="font-extrabold"
                >
                  {{ learnableMove.move.isZmove ? 'Z' : 'D' }}
                </sup>
              </TableCell>
              <TableCell>
                <ElementalTypeBadges :types="[learnableMove.move.type]" />
              </TableCell>
              <TableCell>
                {{
                  learnableMove.move.category === MoveCategory.OHKO
                    ? 'One-hit KO'
                    : learnableMove.move.power
                }}
              </TableCell>
              <TableCell>{{ learnableMove.move.accuracy }}%</TableCell>
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
              <TableHead>TM</TableHead>
              <TableHead>Move</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Power</TableHead>
              <TableHead>Accuracy</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="learnableMove in learnableMoves.tm"
              :key="learnableMove.move.name"
            >
              <TableCell>{{ learnableMove.tmCode }}</TableCell>
              <TableCell>{{ learnableMove.move.name }}</TableCell>
              <TableCell>
                <ElementalTypeBadges :types="[learnableMove.move.type]" />
              </TableCell>
              <TableCell>
                {{
                  learnableMove.move.category === MoveCategory.OHKO
                    ? 'One-hit KO'
                    : learnableMove.move.power
                }}
              </TableCell>
              <TableCell>{{ learnableMove.move.accuracy }}%</TableCell>
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
import type { DexPokedexShowPageProps } from '@/pages/Dex/Pokedex/Show.vue';
import { MoveCategory } from '@/types';
import ElementalTypeBadges from './ElementalTypeBadges.vue';

defineProps<{
  speciesName: string;
  learnableMoves: DexPokedexShowPageProps['learnableMoves'];
}>();
</script>
