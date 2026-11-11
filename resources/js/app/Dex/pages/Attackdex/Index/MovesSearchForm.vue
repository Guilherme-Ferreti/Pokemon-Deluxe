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
    <MovesSearchFiltersDialog
      v-model:types="searchForm.types"
      v-model:ailments="searchForm.ailments"
      v-model:traits="searchForm.traits"
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
import type { AttackdexIndexPageProps } from '../../../types/page-props/AttackdexIndexPageProps';
import MovesSearchFiltersDialog from './MovesSearchFiltersDialog.vue';

const page = usePage<AttackdexIndexPageProps>();

const { searchForm, submitSearch, resetUnappliedChanges } = useSearchForm(
  page.props.filters.active,
  route('dex.web.attackdex.index'),
  ['moves', 'filters.active'],
);

defineExpose({
  searchForm,
});
</script>
