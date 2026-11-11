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
      <PokemonSearchFiltersDialog
        v-model:types="searchForm.types"
        v-model:traits="searchForm.traits"
        v-model:generations="searchForm.generations"
        @apply="submitSearch"
        @close="resetUnappliedChanges"
      />
      <PokemonSortPopover
        v-model:sort="searchForm.sort"
        v-model:dir="searchForm.dir"
        @submit="submitSearch"
        @close="resetUnappliedChanges"
      />
    </Card>
  </form>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconSearch } from '@tabler/icons-vue';
import AppInput from '@/shared/components/form/AppInput.vue';
import { Card } from '@/shared/components/ui/card';
import { useSearchForm } from '@/shared/composables/useSearchForm';
import { route } from '@/shared/types/helpers/route.js';
import type { BoxIndexPageProps } from '../../../types/page-props/BoxIndexPageProps';
import PokemonSearchFiltersDialog from './PokemonSearchFiltersDialog.vue';
import PokemonSortPopover from './PokemonSortPopover.vue';

const page = usePage<BoxIndexPageProps>();

const { searchForm, submitSearch, resetUnappliedChanges } = useSearchForm(
  page.props.filters.active,
  route('pokemon.web.box.index'),
  ['pokemonList', 'filters.active'],
);
</script>
