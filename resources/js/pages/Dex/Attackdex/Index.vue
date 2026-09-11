<template>
  <Head title="Attackdex" />
  <AppPageHeader
    title="Attackdex"
    description="Below is a list of all moves in the game."
  />
  <AppSection>
    <Attackdex
      :moves="moves"
      :active-filters="activeFilters"
      :filter-options="filterOptions"
    />
  </AppSection>
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSection from '@/components/AppSection.vue';
import Attackdex from '@/components/Attackdex.vue';
import type {
  Ailment,
  ElementalType,
  FilterOption,
  MoveCategory,
  PaginatedList,
  SearchAttackdexSortBy,
  SortDirection,
} from '@/types';
import { route } from '@/types/helpers/route';

export interface DexAttackdexIndexPageProps {
  moves: PaginatedList<{
    id: string;
    name: string;
    type: ElementalType;
    category: MoveCategory;
    power?: number;
    accuracy: number;
    ailment?: Ailment;
    ailmentChance?: number;
    flinchChance?: number;
    isZmove: boolean;
  }>;
  activeFilters: {
    search: string;
    types: ElementalType[];
    ailments: Ailment[];
    sort: SearchAttackdexSortBy;
    dir: SortDirection;
  };
  filterOptions: {
    types: FilterOption<ElementalType>[];
    ailments: FilterOption<Ailment>[];
  };
}

defineProps<DexAttackdexIndexPageProps>();

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Attackdex',
      href: route('dex.web.attackdex.index'),
    },
    {
      name: 'List',
    },
  ],
});
</script>
