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
        @apply="submitSearch"
        @close="nextTick(() => !searchForm.processing && searchForm.reset())"
      />
      <PokemonBoxSearchBarSortPopover
        v-model:sort="searchForm.sort"
        v-model:dir="searchForm.dir"
        @submit="submitSearch"
        @close="nextTick(() => !searchForm.processing && searchForm.reset())"
      />
    </Card>
  </form>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconSearch } from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { nextTick } from 'vue';
import PokemonBoxSearchBarFiltersDialog from '@/app/Pokemon/components/PokemonBoxSearchBarFiltersDialog.vue';
import PokemonBoxSearchBarSortPopover from '@/app/Pokemon/components/PokemonBoxSearchBarSortPopover.vue';
import type { BoxIndexPageProps } from '@/app/Pokemon/types/page-props/BoxIndexPageProps';
import AppInput from '@/shared/components/AppInput.vue';
import { Card } from '@/shared/components/ui/card';
import { removeFalsyValues } from '@/shared/lib/utils';
import { route } from '@/shared/types/helpers/route.js';

const page = usePage<BoxIndexPageProps>();

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
  searchForm.transform(removeFalsyValues).get(route('pokemon.web.box.index'), {
    preserveScroll: true,
    preserveState: true,
    only: ['pokemonList', 'filters.active'],
  });
}
</script>
