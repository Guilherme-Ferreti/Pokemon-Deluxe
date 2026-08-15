<template>
  <Head title="Pokedex" />
  <AppPageHeader
    title="Pokedex"
    description="Below is a list of all Pokemon in the game."
  />
  <p>
    The <AppIconInBox /> icon indicates pokemon you have in your box.
    <br />
    The <AppIconNotInBox /> icon indicates pokemon you do not have in your box.
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
              v-for="species in speciesList.data"
              :key="species.id"
            >
              <TableCell class="text-muted-foreground">
                {{ species.pokedexNumber }}
              </TableCell>
              <TableCell>
                <img
                  class="size-8"
                  loading="lazy"
                  :src="species.portrait"
                  :alt="`${species.name}'s portrait`"
                />
              </TableCell>
              <TableCell class="font-medium whitespace-normal">
                {{ species.name }}
              </TableCell>
              <TableCell>
                <ElementalTypeBadges :types="species.types" />
              </TableCell>
              <TableCell class="text-muted-foreground">
                {{ species.generation }}
              </TableCell>
              <TableCell>
                <AppIconInBox v-if="species.regularInBox" />
                <AppIconNotInBox v-else />
              </TableCell>
              <TableCell>
                <AppIconInBox v-if="species.shinyInBox" />
                <AppIconNotInBox v-else />
              </TableCell>
              <TableCell>Actions</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
    <AppPagination :list="speciesList" />
  </AppSection>
</template>

<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';
import AppIconInBox from '@/components/AppIconInBox.vue';
import AppIconNotInBox from '@/components/AppIconNotInBox.vue';
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
  speciesList: PaginatedList<{
    id: string;
    portrait: string;
    name: string;
    frontSprite: string;
    pokedexNumber: number;
    types: [ElementalType] | [ElementalType, ElementalType];
    generation: string;
    regularInBox: boolean;
    shinyInBox: boolean;
  }>;
}>();
</script>
