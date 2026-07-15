<template>
  <form @submit.prevent="submitSearch">
    <Card class="grid grid-cols-[1fr_auto] gap-2 p-4">
      <AppInput
        v-model="searchForm.search"
        type="search"
        name="search"
        placeholder="Search by name..."
        :icon="IconSearch"
      />
      <AppDialog
        title="Filters"
        description="Use these filters to narrow down your search."
        size="lg"
        @close="
          nextTick(() =>
            searchForm.processing ? undefined : searchForm.reset(),
          )
        "
      >
        <template #trigger>
          <AppTooltip text="Filters">
            <div class="relative">
              <Button
                type="button"
                variant="outline"
                size="icon"
                aria-label="Filters"
              >
                <IconFilter2 />
              </Button>
              <Badge
                v-if="activeFiltersCount > 0"
                variant="destructive"
                class="absolute top-0 right-0 size-4 translate-x-1/2 -translate-y-1/2 transform rounded-full font-mono font-semibold tabular-nums"
              >
                {{ activeFiltersCount }}
              </Badge>
            </div>
          </AppTooltip>
        </template>
        <div class="dialog-filters__wrapper">
          <div class="dialog-filters__filters-group">
            <p class="dialog-filters__filters-group-title">By type:</p>
            <div class="dialog-filters__filters-group-checkboxes">
              <AppCheckbox
                v-for="option in elementalTypeOptions"
                :key="option.label"
                v-model="searchForm.elementalTypes"
                name="elementalTypes[]"
                :label="option.label"
                :value="option.value"
                :disabled="
                  searchForm.elementalTypes.length >= 2 &&
                  !searchForm.elementalTypes.includes(option.value)
                "
              />
            </div>
          </div>
          <div class="dialog-filters__filters-group">
            <p class="dialog-filters__filters-group-title">
              By characteristic:
            </p>
            <div class="dialog-filters__filters-group-checkboxes">
              <AppCheckbox
                v-for="option in characteristicOptions"
                :key="option.label"
                v-model="searchForm.characteristics"
                name="characteristics[]"
                :label="option.label"
                :value="option.value"
                :disabled="isCharacteristicCheckboxDisabled(option.value)"
              />
            </div>
          </div>
          <div class="dialog-filters__filters-group">
            <p class="dialog-filters__filters-group-title">By generation:</p>
            <div class="dialog-filters__filters-group-checkboxes">
              <AppCheckbox
                v-for="option in generationOptions"
                :key="option.label"
                v-model="searchForm.generations"
                name="generations[]"
                :label="option.label"
                :value="option.value"
              />
            </div>
          </div>
        </div>
        <template #footer>
          <DialogClose as-child>
            <Button
              type="button"
              variant="outline"
              @click="(clearSearchAndDialogFilters(), nextTick(submitSearch))"
            >
              Clear all
            </Button>
          </DialogClose>
          <DialogClose as-child>
            <Button
              type="button"
              variant="outline"
              class="sm:ml-auto"
            >
              Cancel
            </Button>
            <DialogClose as-child>
              <Button
                type="button"
                @click="submitSearch"
              >
                Apply
              </Button>
            </DialogClose>
          </DialogClose>
        </template>
      </AppDialog>
    </Card>
  </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { IconFilter2, IconSearch } from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { computed, nextTick } from 'vue';
import { Card } from '@/components/ui/card';
import type { PokemonIndexPageProps } from '@/pages/Pokemon/Index.vue';
import { route } from '@/types/helpers/route.js';
import {
  ElementalType,
  Generation,
  PokemonCharacteristic,
} from '@/types/pokemon.js';
import AppCheckbox from './AppCheckbox.vue';
import AppDialog from './AppDialog.vue';
import AppInput from './AppInput.vue';
import AppTooltip from './AppTooltip.vue';
import Badge from './ui/badge/Badge.vue';
import { Button } from './ui/button';
import DialogClose from './ui/dialog/DialogClose.vue';

const props = defineProps<{
  searchFilters: PokemonIndexPageProps['searchFilters'];
}>();

const elementalTypeOptions = [
  { label: 'Bug', value: ElementalType.BUG },
  { label: 'Dark', value: ElementalType.DARK },
  { label: 'Dragon', value: ElementalType.DRAGON },
  { label: 'Electric', value: ElementalType.ELECTRIC },
  { label: 'Fairy', value: ElementalType.FAIRY },
  { label: 'Fighting', value: ElementalType.FIGHTING },
  { label: 'Fire', value: ElementalType.FIRE },
  { label: 'Ghost', value: ElementalType.GHOST },
  { label: 'Grass', value: ElementalType.GRASS },
  { label: 'Ground', value: ElementalType.GROUND },
  { label: 'Ice', value: ElementalType.ICE },
  { label: 'Normal', value: ElementalType.NORMAL },
  { label: 'Poison', value: ElementalType.POISON },
  { label: 'Psychic', value: ElementalType.PSYCHIC },
  { label: 'Rock', value: ElementalType.ROCK },
  { label: 'Steel', value: ElementalType.STEEL },
  { label: 'Water', value: ElementalType.WATER },
];

const characteristicOptions = [
  { label: 'Shiny', value: PokemonCharacteristic.SHINY },
  { label: 'Baby', value: PokemonCharacteristic.BABY },
  { label: 'Legendary', value: PokemonCharacteristic.LEGENDARY },
  { label: 'Mythical', value: PokemonCharacteristic.MYTHICAL },
  { label: 'Mega', value: PokemonCharacteristic.MEGA },
  { label: 'Gmax', value: PokemonCharacteristic.GMAX },
] as const;

const generationOptions = [
  { label: 'Kanto', value: Generation.KANTO },
  { label: 'Johto', value: Generation.JOHTO },
  { label: 'Hoenn', value: Generation.HOENN },
  { label: 'Sinnoh', value: Generation.SINNOH },
  { label: 'Unova', value: Generation.UNOVA },
  { label: 'Kalos', value: Generation.KALOS },
  { label: 'Alola', value: Generation.ALOLA },
  { label: 'Galar', value: Generation.GALAR },
  { label: 'Paldea', value: Generation.PALDEA },
] as const;

const searchForm = useForm({
  search: props.searchFilters.search,
  elementalTypes: props.searchFilters.elementalTypes,
  characteristics: props.searchFilters.characteristics,
  generations: props.searchFilters.generations,
});

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
);

const activeFiltersCount = computed(
  () =>
    props.searchFilters.generations.length +
    props.searchFilters.characteristics.length +
    props.searchFilters.elementalTypes.length,
);

function includesAny(...values: PokemonCharacteristic[]) {
  return searchForm.characteristics.some((value) => values.includes(value));
}

function isCharacteristicCheckboxDisabled(value: PokemonCharacteristic) {
  if (value === PokemonCharacteristic.BABY) {
    return includesAny(
      PokemonCharacteristic.MEGA,
      PokemonCharacteristic.GMAX,
      PokemonCharacteristic.LEGENDARY,
      PokemonCharacteristic.MYTHICAL,
    );
  }

  if (value === PokemonCharacteristic.LEGENDARY) {
    return includesAny(
      PokemonCharacteristic.MYTHICAL,
      PokemonCharacteristic.BABY,
    );
  }

  if (value === PokemonCharacteristic.MYTHICAL) {
    return includesAny(
      PokemonCharacteristic.LEGENDARY,
      PokemonCharacteristic.BABY,
    );
  }

  if (value === PokemonCharacteristic.MEGA) {
    return includesAny(PokemonCharacteristic.GMAX, PokemonCharacteristic.BABY);
  }

  if (value === PokemonCharacteristic.GMAX) {
    return includesAny(PokemonCharacteristic.MEGA, PokemonCharacteristic.BABY);
  }

  return false;
}

function clearSearchAndDialogFilters() {
  searchForm.search = '';
  searchForm.characteristics = [];
  searchForm.generations = [];
  searchForm.elementalTypes = [];
}

function submitSearch() {
  if (!searchForm.isDirty) {
    return;
  }

  searchForm
    .transform((data) =>
      Object.fromEntries(
        Object.entries(data).filter(([, value]) => value || false),
      ),
    )
    .get(route('web.pokemon.index'), {
      preserveScroll: true,
      preserveState: true,
      only: ['pokemonInBox', 'searchFilters'],
    });
}
</script>

<style scoped>
@reference '../../css/app.css';

.dialog-filters__wrapper {
  @apply space-y-6;
}

.dialog-filters__filters-group {
  @apply grid gap-4 sm:grid-cols-[1fr_3fr] sm:gap-4;
}

.dialog-filters__filters-group-title {
  @apply text-sm font-semibold;
}

.dialog-filters__filters-group-checkboxes {
  @apply grid grid-cols-2 gap-4 sm:grid-cols-3;
}
</style>
