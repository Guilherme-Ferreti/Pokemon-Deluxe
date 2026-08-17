<template>
  <Card class="p-4">
    <AppInput
      v-model="searchForm.search"
      type="search"
      name="search"
      placeholder="Search by name..."
      :icon="IconSearch"
    />
  </Card>
  <Card class="p-4">
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
            <AppSpeciesPortrait
              :name="species.name"
              :url="species.portrait"
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
  </Card>
  <AppPagination :list="speciesList" />
</template>

<script lang="ts" setup>
import { useForm } from '@inertiajs/vue3';
import { IconSearch } from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import AppIconInBox from '@/components/AppIconInBox.vue';
import AppIconNotInBox from '@/components/AppIconNotInBox.vue';
import AppInput from '@/components/AppInput.vue';
import AppPagination from '@/components/AppPagination.vue';
import AppSpeciesPortrait from '@/components/AppSpeciesPortrait.vue';
import ElementalTypeBadges from '@/components/ElementalTypeBadges.vue';
import { Card } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import type { DexPokedexIndexPageProps } from '@/pages/Dex/Pokedex/Index.vue';
import { route } from '@/types/helpers/route';

const props = defineProps<{
  speciesList: DexPokedexIndexPageProps['speciesList'];
  activeFilters: DexPokedexIndexPageProps['activeFilters'];
}>();

const searchForm = useForm({
  search: props.activeFilters.search,
});

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
);

function submitSearch() {
  if (!searchForm.isDirty) {
    return;
  }

  searchForm
    .transform((data) =>
      Object.fromEntries(
        Object.entries(data).filter(([, value]) => value || false),
      ),
    )
    .get(route('web.dex.pokedex.index'), {
      preserveScroll: true,
      preserveState: true,
      only: ['speciesList', 'activeFilters'],
    });
}
</script>
