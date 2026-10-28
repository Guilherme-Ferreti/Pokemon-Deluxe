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
          {{ page.props.species.name }} learns the following moves at the levels
          specified
        </CardDescription>
        <PokedexSpeciesMovesLearnedByLevelUpTable />
      </div>
      <div>
        <span class="text-sm font-bold">By TM</span>
        <CardDescription class="mb-4">
          {{ page.props.species.name }} is compatible with these Technical
          Machines
        </CardDescription>
        <PokedexSpeciesMovesLearnedByTMTable />
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import PokedexSpeciesMovesLearnedByLevelUpTable from '@/app/Dex/components/PokedexSpeciesMovesLearnedByLevelUpTable.vue';
import PokedexSpeciesMovesLearnedByTMTable from '@/app/Dex/components/PokedexSpeciesMovesLearnedByTMTable.vue';
import type { PokedexShowPageProps } from '@/app/Dex/types/page-props/PokedexShowPageProps';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';

const page = usePage<PokedexShowPageProps>();
</script>
