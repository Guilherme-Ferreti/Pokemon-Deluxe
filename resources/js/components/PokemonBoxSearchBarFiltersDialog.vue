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
import type { PokemonBoxIndexPageProps } from '@/pages/Pokemon/Box/Index.vue';
import { PokemonTrait } from '@/types/php-types.js';
import AppCheckboxGroup from './AppCheckboxGroup.vue';
import AppSearchFiltersDialog from './AppSearchFiltersDialog.vue';

defineProps<{
  typeOptions: PokemonBoxIndexPageProps['filterOptions']['types'];
  traitOptions: PokemonBoxIndexPageProps['filterOptions']['traits'];
  generationOptions: PokemonBoxIndexPageProps['filterOptions']['generations'];
  activeFiltersCount: number;
}>();

const selectedTypes = defineModel<
  PokemonBoxIndexPageProps['activeFilters']['types']
>('types', {
  default: () => [],
});

const selectedTraits = defineModel<
  PokemonBoxIndexPageProps['activeFilters']['traits']
>('traits', {
  default: () => [],
});

const selectedGenerations = defineModel<
  PokemonBoxIndexPageProps['activeFilters']['generations']
>('generations', {
  default: () => [],
});

defineEmits<{
  apply: [value: void];
  close: [value: void];
}>();

function includesAny(...values: string[]) {
  return selectedTraits.value.some((value) => values.includes(value));
}

function isTraitCheckboxDisabled(value: PokemonTrait) {
  if (value === PokemonTrait.BABY) {
    return includesAny(
      PokemonTrait.MEGA,
      PokemonTrait.GMAX,
      PokemonTrait.LEGENDARY,
      PokemonTrait.MYTHICAL,
    );
  }

  if (value === PokemonTrait.LEGENDARY) {
    return includesAny(PokemonTrait.MYTHICAL, PokemonTrait.BABY);
  }

  if (value === PokemonTrait.MYTHICAL) {
    return includesAny(PokemonTrait.LEGENDARY, PokemonTrait.BABY);
  }

  if (value === PokemonTrait.MEGA) {
    return includesAny(PokemonTrait.GMAX, PokemonTrait.BABY);
  }

  if (value === PokemonTrait.GMAX) {
    return includesAny(PokemonTrait.MEGA, PokemonTrait.BABY);
  }

  return false;
}

function resetFilters() {
  selectedTraits.value = [];
  selectedTypes.value = [];
  selectedGenerations.value = [];
}
</script>
