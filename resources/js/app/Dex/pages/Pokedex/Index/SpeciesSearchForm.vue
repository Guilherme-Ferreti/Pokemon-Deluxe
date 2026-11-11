<template>
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
      @keypress.enter.prevent="searchForm.isDirty && submitSearch()"
    />
    <SpeciesFiltersDialog
      v-model:types="searchForm.types"
      v-model:traits="searchForm.traits"
      v-model:generations="searchForm.generations"
      @apply="submitSearch"
      @close="resetUnappliedChanges"
    />
  </form>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconSearch } from '@tabler/icons-vue';
import AppInput from '@/shared/components/form/AppInput.vue';
import { useSearchForm } from '@/shared/composables/useSearchForm';
import { route } from '@/shared/types/helpers/route';
import type { PokedexIndexPageProps } from '../../../types/page-props/PokedexIndexPageProps';
import SpeciesFiltersDialog from './SpeciesFiltersDialog.vue';

const page = usePage<PokedexIndexPageProps>();

const { searchForm, submitSearch, resetUnappliedChanges } = useSearchForm(
  page.props.filters.active,
  route('dex.web.pokedex.index'),
  ['speciesList', 'filters.active'],
);

defineExpose({
  searchForm,
});
</script>
