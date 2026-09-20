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
      <AttackdexSearchBarFiltersDialog
        v-model:types="searchForm.types"
        v-model:ailments="searchForm.ailments"
        v-model:traits="searchForm.traits"
        :type-options="filterOptions.types"
        :ailment-options="filterOptions.ailments"
        :trait-options="filterOptions.traits"
        :active-filters-count="
          activeFilters.types.length +
          activeFilters.ailments.length +
          activeFilters.traits.length
        "
        @apply="submitSearch"
        @close="nextTick(() => !searchForm.processing && searchForm.reset())"
      />
    </form>
    <Table
      v-if="moves.data.length > 0"
      class="table-content-centered table-empty-cell-placeholder"
    >
      <TableHeader>
        <TableRow>
          <AppTableHeadSortableLink
            class="w-55"
            column-name="Move"
            :sort-by="SearchAttackdexSortBy.NAME"
            :default-sort-by="SearchAttackdexSortBy.NAME"
            :search-form="searchForm"
            :route-url="route('dex.web.attackdex.index')"
            :sort-asc-icon="IconSortAscendingLetters"
            :sort-desc-icon="IconSortDescendingLetters"
          />
          <TableHead>Type</TableHead>
          <AppTableHeadSortableLink
            column-name="Power"
            :sort-by="SearchAttackdexSortBy.POWER"
            :default-sort-by="SearchAttackdexSortBy.NAME"
            :search-form="searchForm"
            :route-url="route('dex.web.attackdex.index')"
          />
          <AppTableHeadSortableLink
            column-name="Accuracy"
            :sort-by="SearchAttackdexSortBy.ACCURACY"
            :default-sort-by="SearchAttackdexSortBy.NAME"
            :search-form="searchForm"
            :route-url="route('dex.web.attackdex.index')"
          />
          <TableHead>Ailment</TableHead>
          <AppTableHeadSortableLink
            column-name="Ailment chance"
            :sort-by="SearchAttackdexSortBy.AILMENT_CHANCE"
            :default-sort-by="SearchAttackdexSortBy.NAME"
            :search-form="searchForm"
            :route-url="route('dex.web.attackdex.index')"
          />
          <AppTableHeadSortableLink
            column-name="Flinch chance"
            :sort-by="SearchAttackdexSortBy.FLINCH_CHANCE"
            :default-sort-by="SearchAttackdexSortBy.NAME"
            :search-form="searchForm"
            :route-url="route('dex.web.attackdex.index')"
          />
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="move in moves.data"
          :key="move.id"
        >
          <TableCell>
            <MoveNameLabel
              :name="move.name"
              :is-z-move="move.isZMove"
              :is-dynamax-move="move.isDynamaxMove"
            />
          </TableCell>
          <TableCell>
            <ElementalTypeBadges :types="[move.type]" />
          </TableCell>
          <TableCell>
            <MovePowerLabel
              :power="move.power"
              :category="move.category"
            />
          </TableCell>
          <TableCell>{{ move.accuracy }}%</TableCell>
          <TableCell>
            {{ move.ailment ? $page.props.labels.ailment[move.ailment] : '' }}
          </TableCell>
          <TableCell>
            {{ move.ailmentChance ? `${move.ailmentChance}%` : '' }}
          </TableCell>
          <TableCell>
            {{ move.flinchChance ? `${move.flinchChance}%` : '' }}
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
    <AppEmptyState
      v-else
      title="No moves found"
      description="Change your search or filters to find some."
      data-test="attackdex__empty-state"
      :icon="IconXMark"
    />
  </Card>
  <AppPagination :list="moves" />
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
  IconSearch,
  IconSortAscendingLetters,
  IconSortDescendingLetters,
  IconXMark,
} from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { nextTick } from 'vue';
import AttackdexSearchBarFiltersDialog from '@/app/Dex/components/AttackdexSearchBarFiltersDialog.vue';
import type { DexAttackdexIndexPageProps } from '@/app/Dex/types/AttackdexIndexPageProps';
import AppEmptyState from '@/shared/components/AppEmptyState.vue';
import AppInput from '@/shared/components/AppInput.vue';
import AppPagination from '@/shared/components/AppPagination.vue';
import AppTableHeadSortableLink from '@/shared/components/AppTableHeadSortableLink.vue';
import ElementalTypeBadges from '@/shared/components/ElementalTypeBadges.vue';
import MoveNameLabel from '@/shared/components/MoveNameLabel.vue';
import MovePowerLabel from '@/shared/components/MovePowerLabel.vue';
import { Card } from '@/shared/components/ui/card';
import {
  TableCell,
  TableRow,
  Table,
  TableHeader,
  TableHead,
  TableBody,
} from '@/shared/components/ui/table';
import { removeFalsyValues } from '@/shared/lib/utils';
import { SearchAttackdexSortBy } from '@/shared/types';
import { route } from '@/shared/types/helpers/route';

const props = defineProps<{
  moves: DexAttackdexIndexPageProps['moves'];
  activeFilters: DexAttackdexIndexPageProps['activeFilters'];
  filterOptions: DexAttackdexIndexPageProps['filterOptions'];
}>();

const searchForm = useForm({
  search: props.activeFilters.search,
  types: props.activeFilters.types,
  ailments: props.activeFilters.ailments,
  traits: props.activeFilters.traits,
  sort: props.activeFilters.sort,
  dir: props.activeFilters.dir,
});

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
);

function submitSearch() {
  searchForm
    .transform(removeFalsyValues)
    .get(route('dex.web.attackdex.index'), {
      preserveScroll: true,
      preserveState: true,
      only: ['moves', 'activeFilters'],
    });
}
</script>
