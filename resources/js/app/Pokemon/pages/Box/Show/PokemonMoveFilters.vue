<template>
  <div class="flex flex-col gap-2">
    <AppFilterChipGroup
      v-model="sortOrder"
      label="Sort by"
      visible-label="Sort by"
      :options="sortOptions"
      :test-id-prefix="`${testIdBlock}__sort`"
    />
    <AppFilterChipGroup
      v-model="selectedType"
      label="Filter by type"
      visible-label="Filter by"
      all-label="All types"
      :options="typeOptions"
      :test-id-prefix="`${testIdBlock}__type`"
    />
  </div>
</template>

<script setup lang="ts">
import AppFilterChipGroup from '@/shared/components/list/AppFilterChipGroup.vue';
import type { FilterOption } from '@/shared/types';
import type { PokemonType } from '@/shared/types/enums';
import type { MoveSortOrder } from '../../../composables/useMoveFilters';

defineProps<{
  typeOptions: FilterOption<PokemonType>[];
  testIdBlock: string;
}>();

const sortOrder = defineModel<MoveSortOrder>('sortOrder', { required: true });

const selectedType = defineModel<PokemonType | null>('selectedType', {
  required: true,
});

const sortOptions: FilterOption<MoveSortOrder>[] = [
  { value: 'name', label: 'Name' },
  { value: 'power', label: 'Power' },
];
</script>
