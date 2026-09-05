<template>
  <form @submit.prevent="submitSearch">
    <Card class="flex-row gap-2 p-4">
      <AppInput
        v-model="searchForm.search"
        type="search"
        name="search"
        placeholder="Search by name..."
        :icon="IconSearch"
        @keypress.enter.prevent="() => searchForm.isDirty && submitSearch()"
      />
      <PokemonBoxSearchBarFiltersDialog
        v-model:types="searchForm.types"
        v-model:traits="searchForm.traits"
        v-model:generations="searchForm.generations"
        :type-options="filterOptions.types"
        :trait-options="filterOptions.traits"
        :generation-options="filterOptions.generations"
        :active-filters-count="
          activeFilters.types.length +
          activeFilters.traits.length +
          activeFilters.generations.length
        "
        @apply="submitSearch"
        @close="nextTick(() => !searchForm.processing && searchForm.reset())"
      />
      <PokemonBoxSearchBarSortPopover
        v-model:sort="searchForm.sort"
        v-model:dir="searchForm.dir"
        :sort-options="filterOptions.sort"
        :dir-options="filterOptions.dir"
        @submit="submitSearch"
        @close="nextTick(() => !searchForm.processing && searchForm.reset())"
      />
    </Card>
  </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { IconSearch } from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { nextTick } from 'vue';
import AppInput from '@/components/AppInput.vue';
import PokemonBoxSearchBarFiltersDialog from '@/components/PokemonBoxSearchBarFiltersDialog.vue';
import PokemonBoxSearchBarSortPopover from '@/components/PokemonBoxSearchBarSortPopover.vue';
import { Card } from '@/components/ui/card';
import { removeFalsyValues } from '@/lib/utils';
import type { PokemonBoxIndexPageProps } from '@/pages/Pokemon/Box/Index.vue';
import { route } from '@/types/helpers/route.js';

const props = defineProps<{
  activeFilters: PokemonBoxIndexPageProps['activeFilters'];
  filterOptions: PokemonBoxIndexPageProps['filterOptions'];
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

function submitSearch() {
  searchForm.transform(removeFalsyValues).get(route('pokemon.web.box.index'), {
    preserveScroll: true,
    preserveState: true,
    only: ['pokemonList', 'activeFilters'],
  });
}
</script>
