<template>
  <AppSearchFiltersDialog
    tooltip-text="Filters"
    dialog-title="Filters"
    dialog-description="Use these filters to narrow down your search."
    :active-filters-count="
      page.props.filters.active.types.length +
      page.props.filters.active.traits.length +
      page.props.filters.active.generations.length
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
        :disabled="
          selectedTypes.length >= 2 && !selectedTypes.includes(option.value)
        "
      />
    </AppCheckboxGroup>
    <AppCheckboxGroup title="By trait:">
      <AppCheckbox
        v-for="option in page.props.filters.options.traits"
        :key="option.value"
        v-model="selectedTraits"
        name="traits[]"
        :label="option.label"
        :value="option.value"
        :disabled="isTraitExcludedBySelection(option.value, selectedTraits)"
      />
    </AppCheckboxGroup>
    <AppCheckboxGroup title="By generation:">
      <AppCheckbox
        v-for="option in page.props.filters.options.generations"
        :key="option.value"
        v-model="selectedGenerations"
        name="generations[]"
        :label="option.label"
        :value="option.value"
      />
    </AppCheckboxGroup>
  </AppSearchFiltersDialog>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { nextTick } from 'vue';
import AppCheckbox from '@/shared/components/form/AppCheckbox.vue';
import AppCheckboxGroup from '@/shared/components/form/AppCheckboxGroup.vue';
import AppSearchFiltersDialog from '@/shared/components/list/AppSearchFiltersDialog.vue';
import { isTraitExcludedBySelection } from '@/shared/lib/traits';
import type { BoxIndexPageProps } from '../../../types/page-props/BoxIndexPageProps';

const page = usePage<BoxIndexPageProps>();

const selectedTypes = defineModel<
  BoxIndexPageProps['filters']['active']['types']
>('types', {
  default: () => [],
});

const selectedTraits = defineModel<
  BoxIndexPageProps['filters']['active']['traits']
>('traits', {
  default: () => [],
});

const selectedGenerations = defineModel<
  BoxIndexPageProps['filters']['active']['generations']
>('generations', {
  default: () => [],
});

defineEmits<{
  apply: [value: void];
  close: [value: void];
}>();
function resetFilters() {
  selectedTraits.value = [];
  selectedTypes.value = [];
  selectedGenerations.value = [];
}
</script>
