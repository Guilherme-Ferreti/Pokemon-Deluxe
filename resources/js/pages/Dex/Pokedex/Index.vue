<template>
  <Head title="Pokedex" />
  <AppPageHeader
    title="Pokedex"
    description="Below is a list of all Pokemon in the game."
  />
  <p>
    The
    <img
      src="/assets/icons/pokedex-in-box.png"
      alt="In Box"
      class="inline-block size-6"
      loading="lazy"
    />
    icon indicates pokemon you have in your box.
  </p>
  <AppSection>
    <Card class="py-3">
      <CardContent class="px-3">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-12 whitespace-normal">Dex No.</TableHead>
              <TableHead>Portrait</TableHead>
              <TableHead>Name</TableHead>
              <TableHead>Type(s)</TableHead>
              <TableHead>Generation</TableHead>
              <TableHead>Regular</TableHead>
              <TableHead>Shiny</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="pokemon in pokedex.data"
              :key="pokemon.id"
            >
              <TableCell class="text-muted-foreground">
                {{ pokemon.pokedexNumber }}
              </TableCell>
              <TableCell>
                <img
                  class="size-8"
                  loading="lazy"
                  :src="pokemon.portrait"
                  :alt="`${pokemon.name}'s portrait`"
                />
              </TableCell>
              <TableCell class="font-medium whitespace-normal">
                {{ pokemon.name }}
              </TableCell>
              <TableCell>
                <ElementalTypeBadges :types="pokemon.types" />
              </TableCell>
              <TableCell class="text-muted-foreground">
                {{ pokemon.generation }}
              </TableCell>
              <TableCell>
                <img
                  src="/assets/icons/pokedex-in-box.png"
                  alt="In Box"
                  class="inline-block size-6"
                />
              </TableCell>
              <TableCell>
                <img
                  src="/assets/icons/pokedex-in-box.png"
                  alt="In Box"
                  class="inline-block size-6"
                />
              </TableCell>
              <TableCell>Actions</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
    <AppPagination :list="pokedex" />
  </AppSection>
</template>

<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppPagination from '@/components/AppPagination.vue';
import AppSection from '@/components/AppSection.vue';
import ElementalTypeBadges from '@/components/ElementalTypeBadges.vue';
import { Card, CardContent } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import type { ElementalType, PaginatedList } from '@/types';

defineOptions({
  layout: () => ({ pageSize: 'small' }),
});

defineProps<{
  pokedex: PaginatedList<{
    id: string;
    portrait: string;
    name: string;
    frontSprite: string;
    pokedexNumber: number;
    types: [ElementalType] | [ElementalType, ElementalType];
    generation: string;
  }>;
}>();
</script>
