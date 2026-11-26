<template>
  <div class="flex flex-col gap-2">
    <div
      class="flex flex-wrap items-center gap-1.5"
      role="group"
      aria-label="Sort by"
    >
      <span class="text-xs text-muted-foreground">Sort by</span>
      <Badge
        v-for="option in sortOptions"
        :key="option.value"
        as="button"
        type="button"
        class="cursor-pointer px-2.5 py-1"
        :variant="sortOrder === option.value ? 'default' : 'outline'"
        :aria-pressed="sortOrder === option.value"
        :data-test="`${testIdBlock}__sort-${option.value}-button`"
        @click="sortOrder = option.value"
      >
        {{ option.label }}
      </Badge>
    </div>
    <div
      class="flex flex-wrap gap-1.5"
      role="group"
      aria-label="Filter by type"
    >
      <Badge
        as="button"
        type="button"
        class="cursor-pointer px-2.5 py-1"
        :variant="selectedType === null ? 'default' : 'outline'"
        :aria-pressed="selectedType === null"
        :data-test="`${testIdBlock}__all-types-button`"
        @click="selectedType = null"
      >
        All types
      </Badge>
      <Badge
        v-for="type in types"
        :key="type"
        as="button"
        type="button"
        class="cursor-pointer px-2.5 py-1"
        :variant="selectedType === type ? 'default' : 'outline'"
        :aria-pressed="selectedType === type"
        :data-test="`${testIdBlock}__type-${type}-button`"
        @click="selectedType = type"
      >
        {{ page.props.labels.pokemonType[type] }}
      </Badge>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Badge } from '@/shared/components/ui/badge';
import type { PokemonType } from '@/shared/types/enums';
import type { MoveSortOrder } from '../../../composables/useMoveFilters';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

defineProps<{
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
</script>
