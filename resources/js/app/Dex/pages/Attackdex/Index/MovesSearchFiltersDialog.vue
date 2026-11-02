<template>
  <AppSearchFiltersDialog
    tooltip-text="Filters"
    dialog-title="Filters"
    dialog-description="Use these filters to narrow down your search."
    :active-filters-count="
      page.props.filters.active.types.length +
      page.props.filters.active.ailments.length +
      page.props.filters.active.traits.length
    "
    @reset="(resetFilters(), nextTick(() => $emit('apply')))"
    @close="$emit('close')"
    @apply="$emit('apply')"
  >
    <AppCheckboxGroup title="By type:">
      <AppCheckbox
        v-for="option in page.props.filters.options.types"
        :key="option.value"
        v-model="selectedTypes"
        name="types[]"
        :label="option.label"
        :value="option.value"
      />
    </AppCheckboxGroup>
    <AppCheckboxGroup title="By ailment:">
      <AppCheckbox
        v-for="option in page.props.filters.options.ailments"
        :key="option.value"
        v-model="selectedAilments"
        name="ailments[]"
        :label="option.label"
        :value="option.value"
      />
    </AppCheckboxGroup>
    <AppCheckboxGroup title="Traits:">
      <AppCheckbox
        v-for="option in page.props.filters.options.traits"
        :key="option.value"
        v-model="selectedTraits"
        name="traits[]"
        :label="option.label"
        :value="option.value"
      />
    </AppCheckboxGroup>
  </AppSearchFiltersDialog>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { nextTick } from 'vue';
import type { AttackdexIndexPageProps } from '@/app/Dex/types/page-props/AttackdexIndexPageProps';
import AppCheckbox from '@/shared/components/form/AppCheckbox.vue';
import AppCheckboxGroup from '@/shared/components/form/AppCheckboxGroup.vue';
import AppSearchFiltersDialog from '@/shared/components/list/AppSearchFiltersDialog.vue';

const page = usePage<AttackdexIndexPageProps>();

const selectedTypes = defineModel<
  AttackdexIndexPageProps['filters']['active']['types']
>('types', {
  default: () => [],
});

const selectedAilments = defineModel<
  AttackdexIndexPageProps['filters']['active']['ailments']
>('ailments', {
  default: () => [],
});

const selectedTraits = defineModel<
  AttackdexIndexPageProps['filters']['active']['traits']
>('traits', {
  default: () => [],
});

defineEmits<{
  apply: [value: void];
  close: [value: void];
}>();

function resetFilters() {
  selectedTypes.value = [];
  selectedAilments.value = [];
  selectedTraits.value = [];
}
</script>
