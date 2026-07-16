<template>
  <form @submit.prevent="submitSearch">
    <Card class="grid grid-cols-[1fr_auto_auto] gap-2 p-4">
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
            <AppIndicator
              :show="activeFiltersCount > 0"
              :text="activeFiltersCount.toString()"
            >
              <Button
                type="button"
                variant="outline"
                size="icon"
                aria-label="Filters"
              >
                <IconFilter2 />
              </Button>
            </AppIndicator>
          </AppTooltip>
        </template>
        <div class="dialog-filters__wrapper">
          <div class="dialog-filters__filters-group">
            <p class="dialog-filters__filters-group-title">By type:</p>
            <div class="dialog-filters__filters-group-checkboxes">
              <AppCheckbox
                v-for="option in filterOptions.types"
                :key="option.value"
                v-model="searchForm.types"
                name="types[]"
                :label="option.label"
                :value="option.value"
                :disabled="
                  searchForm.types.length >= 2 &&
                  !searchForm.types.includes(option.value)
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
                v-for="option in filterOptions.traits"
                :key="option.value"
                v-model="searchForm.traits"
                name="traits[]"
                :label="option.label"
                :value="option.value"
                :disabled="isTraitCheckboxDisabled(option.value)"
              />
            </div>
          </div>
          <div class="dialog-filters__filters-group">
            <p class="dialog-filters__filters-group-title">By generation:</p>
            <div class="dialog-filters__filters-group-checkboxes">
              <AppCheckbox
                v-for="option in filterOptions.generations"
                :key="option.value"
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
          </DialogClose>
          <DialogClose as-child>
            <Button
              type="button"
              @click="submitSearch"
            >
              Apply
            </Button>
          </DialogClose>
        </template>
      </AppDialog>
      <AppPopover
        title="Sort"
        description="Use these options to sort your results."
      >
        <template #trigger>
          <AppTooltip text="Sort">
            <Button
              type="button"
              variant="outline"
              size="icon"
              aria-label="Sort"
            >
              <IconArrowsDownUp />
            </Button>
          </AppTooltip>
        </template>
        <AppSelect
          v-model="searchForm.sort"
          name="sort"
          label="Sort by"
          placeholder="Choose a value"
          :options="filterOptions.sort"
        />
        <AppSelect
          v-model="searchForm.dir"
          name="dir"
          label="Direction"
          placeholder="Choose a value"
          :options="filterOptions.dir"
        />
        <template #footer>
          <PopoverClose as-child>
            <Button
              type="button"
              @click="submitSearch"
            >
              Apply
            </Button>
          </PopoverClose>
        </template>
      </AppPopover>
    </Card>
  </form>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { IconArrowsDownUp, IconFilter2, IconSearch } from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { PopoverClose } from 'reka-ui';
import { computed, nextTick } from 'vue';
import { Card } from '@/components/ui/card';
import { DialogClose } from '@/components/ui/dialog';
import type { PokemonIndexPageProps } from '@/pages/Pokemon/Index.vue';
import { route } from '@/types/helpers/route.js';
import { PokemonTrait } from '@/types/pokemon.js';
import AppCheckbox from './AppCheckbox.vue';
import AppDialog from './AppDialog.vue';
import AppIndicator from './AppIndicator.vue';
import AppInput from './AppInput.vue';
import AppPopover from './AppPopover.vue';
import AppSelect from './AppSelect.vue';
import AppTooltip from './AppTooltip.vue';
import { Button } from './ui/button/index.js';

const props = defineProps<{
  searchFilters: PokemonIndexPageProps['searchFilters'];
  filterOptions: PokemonIndexPageProps['filterOptions'];
}>();

const searchForm = useForm({
  search: props.searchFilters.search,
  types: props.searchFilters.types,
  traits: props.searchFilters.traits,
  generations: props.searchFilters.generations,
  sort: props.searchFilters.sort,
  dir: props.searchFilters.dir,
});

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
);

const activeFiltersCount = computed(
  () =>
    props.searchFilters.generations.length +
    props.searchFilters.traits.length +
    props.searchFilters.types.length,
);

function includesAny(...values: string[]) {
  return searchForm.traits.some((value) => values.includes(value));
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

function clearSearchAndDialogFilters() {
  searchForm.search = '';
  searchForm.traits = [];
  searchForm.generations = [];
  searchForm.types = [];
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
      only: ['pokemonBox', 'searchFilters'],
    });
}
</script>

<style scoped>
@reference '../../css/app.css';

.dialog-filters__wrapper {
  @apply flex flex-col gap-6;
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
