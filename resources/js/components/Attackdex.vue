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
        v-model:isZmove="searchForm.isZmove"
        :type-options="filterOptions.types"
        :ailment-options="filterOptions.ailments"
        :active-filters-count="
          activeFilters.types.length +
          activeFilters.ailments.length +
          (activeFilters.isZmove ? 1 : 0)
        "
        @apply="submitSearch"
        @close="nextTick(() => !searchForm.processing && searchForm.reset())"
      />
    </form>
    <Table
      v-if="moves.data.length > 0"
      class="[&_td]:empty:before:text-muted-foreground [&_td]:empty:before:content-['-'] [&_th,&_tr]:text-center [&_th>a,&_td>div]:justify-center"
    >
      <TableHeader>
        <TableRow>
          <AppTableHeadSortable
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
          <AppTableHeadSortable
            column-name="Power"
            :sort-by="SearchAttackdexSortBy.POWER"
            :default-sort-by="SearchAttackdexSortBy.NAME"
            :search-form="searchForm"
            :route-url="route('dex.web.attackdex.index')"
          />
          <AppTableHeadSortable
            column-name="Accuracy"
            :sort-by="SearchAttackdexSortBy.ACCURACY"
            :default-sort-by="SearchAttackdexSortBy.NAME"
            :search-form="searchForm"
            :route-url="route('dex.web.attackdex.index')"
          />
          <TableHead>Ailment</TableHead>
          <AppTableHeadSortable
            column-name="Ailment chance"
            :sort-by="SearchAttackdexSortBy.AILMENT_CHANCE"
            :default-sort-by="SearchAttackdexSortBy.NAME"
            :search-form="searchForm"
            :route-url="route('dex.web.attackdex.index')"
          />
          <AppTableHeadSortable
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
            {{ move.name }}
            <sup
              v-if="move.isZmove"
              class="font-extrabold"
            >
              Z
            </sup>
          </TableCell>
          <TableCell>
            <ElementalTypeBadges :types="[move.type]" />
          </TableCell>
          <TableCell>
            {{
              move.category === MoveCategory.OHKO ? 'One-hit KO' : move.power
            }}
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
import AppEmptyState from '@/components/AppEmptyState.vue';
import AppInput from '@/components/AppInput.vue';
import AppPagination from '@/components/AppPagination.vue';
import AppTableHeadSortable from '@/components/AppTableHeadSortable.vue';
import AttackdexSearchBarFiltersDialog from '@/components/AttackdexSearchBarFiltersDialog.vue';
import ElementalTypeBadges from '@/components/ElementalTypeBadges.vue';
import { Card } from '@/components/ui/card';
import {
  TableCell,
  TableRow,
  Table,
  TableHeader,
  TableHead,
  TableBody,
} from '@/components/ui/table';
import { removeFalsyValues } from '@/lib/utils';
import type { DexAttackdexIndexPageProps } from '@/pages/Dex/Attackdex/Index.vue';
import { MoveCategory, SearchAttackdexSortBy } from '@/types';
import { route } from '@/types/helpers/route';

const props = defineProps<{
  moves: DexAttackdexIndexPageProps['moves'];
  activeFilters: DexAttackdexIndexPageProps['activeFilters'];
  filterOptions: DexAttackdexIndexPageProps['filterOptions'];
}>();

const searchForm = useForm({
  search: props.activeFilters.search,
  types: props.activeFilters.types,
  ailments: props.activeFilters.ailments,
  isZmove: props.activeFilters.isZmove,
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
