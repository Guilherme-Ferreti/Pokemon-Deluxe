<template>
  <Table
    v-if="page.props.moves.data.length > 0"
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
        v-for="move in page.props.moves.data"
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
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
  IconSortAscendingLetters,
  IconSortDescendingLetters,
  IconXMark,
} from '@tabler/icons-vue';
import { SearchAttackdexSortBy } from '@/app/Dex/types/enums/SearchAttackdexSortBy';
import type { AttackdexIndexPageProps } from '@/app/Dex/types/page-props/AttackdexIndexPageProps';
import AppEmptyState from '@/shared/components/AppEmptyState.vue';
import ElementalTypeBadges from '@/shared/components/ElementalTypeBadges.vue';
import type { AppTableHeadSortableLinkProps } from '@/shared/components/list/AppTableHeadSortableLink.vue';
import AppTableHeadSortableLink from '@/shared/components/list/AppTableHeadSortableLink.vue';
import MoveNameLabel from '@/shared/components/MoveNameLabel.vue';
import MovePowerLabel from '@/shared/components/MovePowerLabel.vue';
import {
  TableCell,
  TableRow,
  Table,
  TableHeader,
  TableHead,
  TableBody,
} from '@/shared/components/ui/table';
import { route } from '@/shared/types/helpers/route';

const page = usePage<AttackdexIndexPageProps>();

defineProps<{
  searchForm: AppTableHeadSortableLinkProps<SearchAttackdexSortBy>['searchForm'];
}>();
</script>
