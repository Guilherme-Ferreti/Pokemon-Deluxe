<template>
  <Dialog @update:open="handleOpenChange">
    <DialogTrigger as-child>
      <Button
        type="button"
        variant="outline"
        data-test="area-species-dialog__open-button"
      >
        Pokémon found here
      </Button>
    </DialogTrigger>
    <DialogScrollContent
      class="flex flex-col gap-4 max-sm:my-0 max-sm:min-h-dvh max-sm:max-w-none max-sm:rounded-none sm:max-w-3xl"
      data-test="area-species-dialog"
    >
      <DialogHeader>
        <DialogTitle
          >Pokémon found in the {{ page.props.area.label }}</DialogTitle
        >
        <DialogDescription class="tabular-nums">
          <template v-if="areaSpecies">
            {{ areaSpecies.length.toLocaleString() }} species
          </template>
          <Skeleton
            v-else
            class="h-2.5 w-16"
          />
        </DialogDescription>
      </DialogHeader>
      <InputGroup class="bg-background shadow-xs">
        <InputGroupInput
          v-model="search"
          type="search"
          placeholder="Search by name"
          autocomplete="off"
          aria-label="Search by name"
          data-test="area-species-dialog__search-input"
        />
        <InputGroupAddon>
          <IconSearch />
        </InputGroupAddon>
      </InputGroup>
      <div
        class="flex flex-wrap gap-1.5"
        role="group"
        aria-label="Filter by type"
      >
        <Badge
          v-for="type in presentTypes"
          :key="type"
          as="button"
          type="button"
          class="cursor-pointer px-2.5 py-1"
          :variant="selectedTypes.includes(type) ? 'default' : 'outline'"
          :aria-pressed="selectedTypes.includes(type)"
          :data-test="`area-species-dialog__type-${type}-button`"
          @click="toggleType(type)"
        >
          {{ page.props.labels.pokemonType[type] }}
        </Badge>
      </div>
      <ul
        v-if="!areaSpecies"
        class="grid grid-cols-[repeat(auto-fill,minmax(4.5rem,1fr))] gap-3"
        aria-busy="true"
      >
        <li
          v-for="index in 12"
          :key="index"
          class="flex flex-col items-center gap-1.5"
        >
          <Skeleton class="size-12" />
          <Skeleton class="h-2.5 w-16" />
        </li>
      </ul>
      <ul
        v-else-if="visibleSpecies.length > 0"
        class="grid grid-cols-[repeat(auto-fill,minmax(4.5rem,1fr))] gap-3"
        data-test="area-species-dialog__species-list"
      >
        <li
          v-for="species in visibleSpecies"
          :key="species.slug"
          class="flex flex-col items-center gap-1.5 text-center"
        >
          <SpeciesPortrait
            class="size-12"
            :url="species.portrait"
            :name="species.name"
          />
          <span class="text-xs font-medium">{{ species.name }}</span>
        </li>
      </ul>
      <AppEmptyState
        v-else
        title="No Pokémon match your search"
        description="Try another name or clear the type filters."
        data-test="area-species-dialog__no-match-empty-state"
        :icon="IconSearch"
      />
    </DialogScrollContent>
  </Dialog>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { IconSearch } from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import AppEmptyState from '@/shared/components/content/AppEmptyState.vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogDescription,
  DialogHeader,
  DialogScrollContent,
  DialogTitle,
  DialogTrigger,
} from '@/shared/components/ui/dialog';
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
} from '@/shared/components/ui/input-group';
import { Skeleton } from '@/shared/components/ui/skeleton';
import { PokemonType } from '@/shared/types/enums';
import type { ExploreShowPageProps } from '../../../types/page-props/ExploreShowPageProps';

const page = usePage<ExploreShowPageProps>();

const search = ref('');

const selectedTypes = ref<PokemonType[]>([]);

const areaSpecies = computed(() => page.props.areaSpecies);

const presentTypes = computed(() => {
  const types = new Set(
    (areaSpecies.value ?? []).flatMap((species) => species.types),
  );

  return Object.values(PokemonType).filter((type) => types.has(type));
});

const visibleSpecies = computed(() => {
  const searchTerm = search.value.trim().toLowerCase();

  return (areaSpecies.value ?? []).filter(
    (species) =>
      species.name.toLowerCase().includes(searchTerm) &&
      (selectedTypes.value.length === 0 ||
        species.types.some(
          (type) => type !== undefined && selectedTypes.value.includes(type),
        )),
  );
});

function toggleType(type: PokemonType) {
  selectedTypes.value = selectedTypes.value.includes(type)
    ? selectedTypes.value.filter((selectedType) => selectedType !== type)
    : [...selectedTypes.value, type];
}

function handleOpenChange(isOpen: boolean) {
  if (isOpen && !areaSpecies.value) {
    router.reload({ only: ['areaSpecies'] });
  }
}
</script>
