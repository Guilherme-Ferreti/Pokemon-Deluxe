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
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppFilterChipGroup from '@/shared/components/list/AppFilterChipGroup.vue';
import type { PokemonType } from '@/shared/types/enums';
import type { MoveSortOrder } from '../../../composables/useMoveFilters';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

const props = defineProps<{
  types: PokemonType[];
  testIdBlock: string;
}>();

const sortOrder = defineModel<MoveSortOrder>('sortOrder', { required: true });

const selectedType = defineModel<PokemonType | null>('selectedType', {
  required: true,
});

const page = usePage<BoxShowPageProps>();

const sortOptions: { value: MoveSortOrder; label: string }[] = [
  { value: 'name', label: 'Name' },
  { value: 'power', label: 'Power' },
];

const typeOptions = computed(() =>
  props.types.map((type) => ({
    value: type,
    label: page.props.labels.pokemonType[type],
  })),
);
</script>
