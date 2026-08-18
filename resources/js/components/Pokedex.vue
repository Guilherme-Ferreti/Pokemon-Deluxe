<template>
  <Card class="gap-4 p-4">
    <AppInput
      v-model="searchForm.search"
      type="search"
      name="search"
      placeholder="Search by name..."
      class="ml-auto max-w-1/3"
      :icon="IconSearch"
    />
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="w-12 whitespace-normal">Dex No.</TableHead>
          <TableHead>Portrait</TableHead>
          <TableHead class="w-50">
            <button
              class="flex items-center gap-2 [&_svg]:size-4"
              type="button"
              @click="sortByName"
            >
              <span>Name</span>
              <IconArrowsSort
                v-if="searchForm.sort !== SearchPokedexSortBy.NAME"
              />
              <IconSortAscendingLetters
                v-else-if="searchForm.dir === SortDirection.ASC"
              />
              <IconSortDescendingLetters
                v-else-if="searchForm.dir === SortDirection.DESC"
              />
            </button>
          </TableHead>
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
import {
  IconArrowsSort,
  IconSearch,
  IconSortAscendingLetters,
  IconSortDescendingLetters,
} from '@tabler/icons-vue';
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
import { SearchPokedexSortBy, SortDirection } from '@/types';
import { route } from '@/types/helpers/route';

const props = defineProps<{
  speciesList: DexPokedexIndexPageProps['speciesList'];
  activeFilters: DexPokedexIndexPageProps['activeFilters'];
}>();

const searchForm = useForm({
  search: props.activeFilters.search,
  sort: props.activeFilters.sort,
  dir: props.activeFilters.dir,
});

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
);

function sortByName() {
  if (searchForm.sort !== SearchPokedexSortBy.NAME) {
    searchForm.sort = SearchPokedexSortBy.NAME;
    searchForm.dir = SortDirection.ASC;
  } else if (searchForm.dir === SortDirection.ASC) {
    searchForm.dir = SortDirection.DESC;
  } else if (searchForm.dir === SortDirection.DESC) {
    searchForm.sort = SearchPokedexSortBy.POKEDEX_NUMBER;
    searchForm.dir = SortDirection.ASC;
  }

  submitSearch();
}

function submitSearch() {
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
