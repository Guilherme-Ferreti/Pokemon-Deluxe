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
      />
    </AppCheckboxGroup>
    <AppCheckboxGroup title="By ailment:">
      <AppCheckbox
        v-for="option in ailmentOptions"
        :key="option.value"
        v-model="selectedAilments"
        name="ailments[]"
        :label="option.label"
        :value="option.value"
      />
    </AppCheckboxGroup>
    <AppCheckboxGroup title="Traits:">
      <AppCheckbox
        v-for="option in traitOptions"
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
import { nextTick } from 'vue';
import type { AttackdexIndexPageProps } from '@/app/Dex/types/AttackdexIndexPageProps';
import AppCheckbox from '@/shared/components/AppCheckbox.vue';
import AppCheckboxGroup from '@/shared/components/AppCheckboxGroup.vue';
import AppSearchFiltersDialog from '@/shared/components/AppSearchFiltersDialog.vue';

defineProps<{
  typeOptions: AttackdexIndexPageProps['filterOptions']['types'];
  ailmentOptions: AttackdexIndexPageProps['filterOptions']['ailments'];
  traitOptions: AttackdexIndexPageProps['filterOptions']['traits'];
  activeFiltersCount: number;
}>();

const selectedTypes = defineModel<
  AttackdexIndexPageProps['activeFilters']['types']
>('types', {
  default: () => [],
});

const selectedAilments = defineModel<
  AttackdexIndexPageProps['activeFilters']['ailments']
>('ailments', {
  default: () => [],
});

const selectedTraits = defineModel<
  AttackdexIndexPageProps['activeFilters']['traits']
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
