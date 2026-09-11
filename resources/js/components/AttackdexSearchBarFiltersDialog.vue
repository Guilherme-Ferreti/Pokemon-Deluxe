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
  </AppSearchFiltersDialog>
</template>

<script setup lang="ts">
import { nextTick } from 'vue';
import AppCheckbox from '@/components/AppCheckbox.vue';
import AppCheckboxGroup from '@/components/AppCheckboxGroup.vue';
import AppSearchFiltersDialog from '@/components/AppSearchFiltersDialog.vue';
import type { DexAttackdexIndexPageProps } from '@/pages/Dex/Attackdex/Index.vue';

defineProps<{
  typeOptions: DexAttackdexIndexPageProps['filterOptions']['types'];
  ailmentOptions: DexAttackdexIndexPageProps['filterOptions']['ailments'];
  activeFiltersCount: number;
}>();

const selectedTypes = defineModel<
  DexAttackdexIndexPageProps['activeFilters']['types']
>('types', {
  default: () => [],
});

const selectedAilments = defineModel<
  DexAttackdexIndexPageProps['activeFilters']['ailments']
>('ailments', {
  default: () => [],
});

defineEmits<{
  apply: [value: void];
  close: [value: void];
}>();

function resetFilters() {
  selectedTypes.value = [];
  selectedAilments.value = [];
}
</script>
