<template>
  <AppSearchFiltersDialog
    tooltip-text="Filters"
    dialog-title="Filters"
    dialog-description="Use these filters to narrow down your search."
    :active-filters-count="activeFiltersCount"
    @reset="(resetFilters(), nextTick(() => $emit('apply')))"
    @close="$emit('close')"
    @apply="$emit('apply')"
  >
    <AppCheckboxGroup title="By type:">
      <AppCheckbox
        v-for="option in typeOptions"
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
        v-for="option in traitOptions"
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
        v-for="option in generationOptions"
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
import { nextTick } from 'vue';
import AppCheckbox from '@/components/AppCheckbox.vue';
import AppCheckboxGroup from '@/components/AppCheckboxGroup.vue';
import AppSearchFiltersDialog from '@/components/AppSearchFiltersDialog.vue';
import { anyItemExists } from '@/lib/utils.js';
import type { DexPokedexIndexPageProps } from '@/pages/Dex/Pokedex/Index.vue';
import { SpeciesTrait } from '@/types/php-types.js';

defineProps<{
  typeOptions: DexPokedexIndexPageProps['filterOptions']['types'];
  traitOptions: DexPokedexIndexPageProps['filterOptions']['traits'];
  generationOptions: DexPokedexIndexPageProps['filterOptions']['generations'];
  activeFiltersCount: number;
}>();

const selectedTypes = defineModel<
  DexPokedexIndexPageProps['activeFilters']['types']
>('types', {
  default: () => [],
});

const selectedTraits = defineModel<
  DexPokedexIndexPageProps['activeFilters']['traits']
>('traits', {
  default: () => [],
});

const selectedGenerations = defineModel<
  DexPokedexIndexPageProps['activeFilters']['generations']
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
