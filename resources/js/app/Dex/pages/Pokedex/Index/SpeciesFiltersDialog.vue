<template>
  <AppSearchFiltersDialog
    tooltip-text="Filters"
    dialog-title="Filters"
    dialog-description="Use these filters to narrow down your search."
    :active-filters-count="
      page.props.filters.active.traits.length +
      page.props.filters.active.types.length +
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
        :disabled="isTraitCheckboxDisabled(option.value)"
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
import { anyItemExists } from '@/shared/lib/utils.js';
import { SpeciesTrait } from '@/shared/types/enums';
import type { PokedexIndexPageProps } from '../../../types/page-props/PokedexIndexPageProps';

const page = usePage<PokedexIndexPageProps>();

const selectedTypes = defineModel<
  PokedexIndexPageProps['filters']['active']['types']
>('types', {
  default: () => [],
});

const selectedTraits = defineModel<
  PokedexIndexPageProps['filters']['active']['traits']
>('traits', {
  default: () => [],
});

const selectedGenerations = defineModel<
  PokedexIndexPageProps['filters']['active']['generations']
>('generations', {
  default: () => [],
});

defineEmits<{
  apply: [value: void];
  close: [value: void];
}>();

function isTraitCheckboxDisabled(value: SpeciesTrait) {
  if (value === SpeciesTrait.BABY) {
    return anyItemExists(selectedTraits.value, [
      SpeciesTrait.MEGA,
      SpeciesTrait.GMAX,
      SpeciesTrait.LEGENDARY,
      SpeciesTrait.MYTHICAL,
    ]);
  }

  if (value === SpeciesTrait.LEGENDARY) {
    return anyItemExists(selectedTraits.value, [
      SpeciesTrait.MYTHICAL,
      SpeciesTrait.BABY,
    ]);
  }

  if (value === SpeciesTrait.MYTHICAL) {
    return anyItemExists(selectedTraits.value, [
      SpeciesTrait.LEGENDARY,
      SpeciesTrait.BABY,
    ]);
  }

  if (value === SpeciesTrait.MEGA) {
    return anyItemExists(selectedTraits.value, [
      SpeciesTrait.GMAX,
      SpeciesTrait.BABY,
    ]);
  }

  if (value === SpeciesTrait.GMAX) {
    return anyItemExists(selectedTraits.value, [
      SpeciesTrait.MEGA,
      SpeciesTrait.BABY,
    ]);
  }

  return false;
}

function resetFilters() {
  selectedTypes.value = [];
  selectedTraits.value = [];
  selectedGenerations.value = [];
}
</script>
