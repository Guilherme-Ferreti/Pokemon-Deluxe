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
      <PokedexSearchFiltersDialog
        v-model:types="searchForm.types"
        v-model:traits="searchForm.traits"
        v-model:generations="searchForm.generations"
        @apply="submitSearch"
        @close="nextTick(() => !searchForm.processing && searchForm.reset())"
      />
    </form>
    <Table v-if="page.props.speciesList.data.length > 0">
      <TableHeader>
        <TableRow>
          <TableHead class="w-12 whitespace-normal">Dex No.</TableHead>
          <TableHead>Portrait</TableHead>
          <AppTableHeadSortableLink
            class="w-50"
            column-name="Name"
            :sort-by="SearchPokedexSortBy.NAME"
            :default-sort-by="SearchPokedexSortBy.POKEDEX_NUMBER"
            :search-form="searchForm"
            :route-url="route('dex.web.pokedex.index')"
            :sort-asc-icon="IconSortAscendingLetters"
            :sort-desc-icon="IconSortDescendingLetters"
          />
          <TableHead>Type(s)</TableHead>
          <TableHead>Generation</TableHead>
          <TableHead>Regular</TableHead>
          <TableHead>Shiny</TableHead>
          <TableHead></TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="species in page.props.speciesList.data"
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
            {{ $page.props.labels.generation[species.generation] }}
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
              data-test="pokemon-box-card__view-species-link"
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
      data-test="pokedex__empty-state"
      :icon="IconXMark"
    />
  </Card>
  <AppPagination :list="page.props.speciesList" />
</template>

<script lang="ts" setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
  IconEye,
  IconSearch,
  IconSortAscendingLetters,
  IconSortDescendingLetters,
  IconXMark,
} from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { nextTick } from 'vue';
import PokedexSearchFiltersDialog from '@/app/Dex/components/PokedexSearchFiltersDialog.vue';
import { SearchPokedexSortBy } from '@/app/Dex/types/enums/SearchPokedexSortBy';
import type { PokedexIndexPageProps } from '@/app/Dex/types/page-props/PokedexIndexPageProps';
import AppEmptyState from '@/shared/components/AppEmptyState.vue';
import AppIconInBox from '@/shared/components/AppIconInBox.vue';
import AppIconNotInBox from '@/shared/components/AppIconNotInBox.vue';
import AppPagination from '@/shared/components/AppPagination.vue';
import AppSpeciesPortrait from '@/shared/components/AppSpeciesPortrait.vue';
import AppTableHeadSortableLink from '@/shared/components/AppTableHeadSortableLink.vue';
import ElementalTypeBadges from '@/shared/components/ElementalTypeBadges.vue';
import AppInput from '@/shared/components/form/AppInput.vue';
import { Card } from '@/shared/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/shared/components/ui/table';
import { removeFalsyValues } from '@/shared/lib/utils.js';
import { route } from '@/shared/types/helpers/route';

const page = usePage<PokedexIndexPageProps>();

const searchForm = useForm({
  search: page.props.filters.active.search,
  types: page.props.filters.active.types,
  traits: page.props.filters.active.traits,
  generations: page.props.filters.active.generations,
  sort: page.props.filters.active.sort,
  dir: page.props.filters.active.dir,
});

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
);

function submitSearch() {
  searchForm.transform(removeFalsyValues).get(route('dex.web.pokedex.index'), {
    preserveScroll: true,
    preserveState: true,
    only: ['speciesList', 'filters.active'],
  });
}
</script>
