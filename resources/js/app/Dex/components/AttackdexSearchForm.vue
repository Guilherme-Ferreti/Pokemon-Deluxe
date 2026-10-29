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
    <AttackdexSearchFiltersDialog
      v-model:types="searchForm.types"
      v-model:ailments="searchForm.ailments"
      v-model:traits="searchForm.traits"
      @apply="submitSearch"
      @close="nextTick(() => !searchForm.processing && searchForm.reset())"
    />
  </form>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconSearch } from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { nextTick } from 'vue';
import AttackdexSearchFiltersDialog from '@/app/Dex/components/AttackdexSearchFiltersDialog.vue';
import type { AttackdexIndexPageProps } from '@/app/Dex/types/page-props/AttackdexIndexPageProps';
import AppInput from '@/shared/components/AppInput.vue';
import { removeFalsyValues } from '@/shared/lib/utils';
import { route } from '@/shared/types/helpers/route';

const page = usePage<AttackdexIndexPageProps>();

const searchForm = useForm({
  search: page.props.filters.active.search,
  types: page.props.filters.active.types,
  ailments: page.props.filters.active.ailments,
  traits: page.props.filters.active.traits,
  sort: page.props.filters.active.sort,
  dir: page.props.filters.active.dir,
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
      only: ['moves', 'filters.active'],
    });
}

defineExpose({
  searchForm,
});
</script>
