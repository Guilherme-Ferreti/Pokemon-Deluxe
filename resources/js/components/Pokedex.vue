<template>
  <Card class="gap-4 p-4">
    <form
      class="flex gap-2"
      @submit.prevent="submitSearch"
    >
      <AppInput
        v-model="searchForm.search"
        type="search"
        name="search"
        placeholder="Search by name..."
        class="ml-auto @xl:max-w-1/3"
        :icon="IconSearch"
        @keypress.enter.prevent="() => searchForm.isDirty && submitSearch()"
      />
      <PokedexSearchBarFiltersDialog
        v-model:types="searchForm.types"
        v-model:traits="searchForm.traits"
        v-model:generations="searchForm.generations"
        :type-options="filterOptions.types"
        :trait-options="filterOptions.traits"
        :generation-options="filterOptions.generations"
        :active-filters-count="
          activeFilters.traits.length +
          activeFilters.types.length +
          activeFilters.generations.length
        "
        @apply="submitSearch"
        @close="nextTick(() => !searchForm.processing && searchForm.reset())"
      />
    </form>
    <Table v-if="speciesList.data.length > 0">
      <TableHeader>
        <TableRow>
          <TableHead class="w-12 whitespace-normal">Dex No.</TableHead>
          <TableHead>Portrait</TableHead>
          <TableHead class="w-50">
            <Link
              class="flex items-center gap-2 [&_svg]:size-4"
              aria-label="Sort by name"
              aria-current="page"
              :href="sortByNameUrl"
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
            </Link>
          </TableHead>
          <TableHead>Type(s)</TableHead>
          <TableHead>Generation</TableHead>
          <TableHead>Regular</TableHead>
          <TableHead>Shiny</TableHead>
          <TableHead></TableHead>
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
          <TableCell>
            <Link
              class="flex items-center gap-1 hover:underline"
              data-test="view-species-link"
              :href="route('dex.web.pokedex.show', { species: species.slug })"
            >
              <IconEye class="size-4" />
              <span aria-hidden="true">View</span>
              <span class="sr-only">View {{ species.name }}</span>
            </Link>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
    <AppEmptyState
      v-else
      title="No Pokémon found"
      description="Change your search or filters to find some."
      data-test="pokedex-empty-state"
      :icon="IconXMark"
    />
  </Card>
  <AppPagination :list="speciesList" />
</template>

<script lang="ts" setup>
import { Link, useForm } from '@inertiajs/vue3';
import {
  IconArrowsSort,
  IconEye,
  IconSearch,
  IconSortAscendingLetters,
  IconSortDescendingLetters,
  IconXMark,
} from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { computed, nextTick } from 'vue';
import AppEmptyState from '@/components/AppEmptyState.vue';
import AppIconInBox from '@/components/AppIconInBox.vue';
import AppIconNotInBox from '@/components/AppIconNotInBox.vue';
import AppInput from '@/components/AppInput.vue';
import AppPagination from '@/components/AppPagination.vue';
import AppSpeciesPortrait from '@/components/AppSpeciesPortrait.vue';
import ElementalTypeBadges from '@/components/ElementalTypeBadges.vue';
import PokedexSearchBarFiltersDialog from '@/components/PokedexSearchBarFiltersDialog.vue';
import { Card } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { removeFalsyValues, toQueryString } from '@/lib/utils.js';
import type { DexPokedexIndexPageProps } from '@/pages/Dex/Pokedex/Index.vue';
import { SearchPokedexSortBy, SortDirection } from '@/types';
import { route } from '@/types/helpers/route';

const props = defineProps<{
  speciesList: DexPokedexIndexPageProps['speciesList'];
  activeFilters: DexPokedexIndexPageProps['activeFilters'];
  filterOptions: DexPokedexIndexPageProps['filterOptions'];
}>();

const searchForm = useForm({
  search: props.activeFilters.search,
  types: props.activeFilters.types,
  traits: props.activeFilters.traits,
  generations: props.activeFilters.generations,
  sort: props.activeFilters.sort,
  dir: props.activeFilters.dir,
});

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
);

const sortByNameUrl = computed(function () {
  const data = searchForm.data();

  if (data.sort !== SearchPokedexSortBy.NAME) {
    data.sort = SearchPokedexSortBy.NAME;
    data.dir = SortDirection.ASC;
  } else if (data.dir === SortDirection.ASC) {
    data.dir = SortDirection.DESC;
  } else if (data.dir === SortDirection.DESC) {
    data.sort = SearchPokedexSortBy.POKEDEX_NUMBER;
    data.dir = SortDirection.ASC;
  }

  return `${route('dex.web.pokedex.index')}?${toQueryString(removeFalsyValues(data)).toString()}`;
});

function submitSearch() {
  searchForm.transform(removeFalsyValues).get(route('dex.web.pokedex.index'), {
    preserveScroll: true,
    preserveState: true,
    only: ['speciesList', 'activeFilters'],
  });
}
</script>
