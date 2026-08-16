<template>
  <form @submit.prevent="submitSearch">
    <Card class="flex-row gap-2 p-4">
      <AppInput
        v-model="searchForm.search"
        type="search"
        name="search"
        placeholder="Search by name..."
        :icon="IconSearch"
      />
      <Dialog
        @close="nextTick(() => !searchForm.processing && searchForm.reset())"
      >
        <Tooltip>
          <AppIndicator
            data-test="active-filters-indicator"
            :show="activeFiltersCount > 0"
            :text="activeFiltersCount.toString()"
          >
            <TooltipTrigger as-child>
              <DialogTrigger as-child>
                <Button
                  type="button"
                  variant="outline"
                  size="icon"
                  aria-label="Filters"
                  data-test="open-search-filters-button"
                >
                  <IconFilter2 />
                </Button>
              </DialogTrigger>
            </TooltipTrigger>
          </AppIndicator>
          <TooltipContent>Filters</TooltipContent>
          <DialogContent class="max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Filters</DialogTitle>
              <DialogDescription>
                Use these filters to narrow down your search.
              </DialogDescription>
            </DialogHeader>
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
                <p class="dialog-filters__filters-group-title">By trait:</p>
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
                <p class="dialog-filters__filters-group-title">
                  By generation:
                </p>
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
            <DialogFooter>
              <DialogClose as-child>
                <Button
                  type="button"
                  variant="outline"
                  data-test="reset-search-filters-button"
                  @click="(clearDialogFilters(), nextTick(submitSearch))"
                >
                  Reset
                </Button>
              </DialogClose>
              <DialogClose as-child>
                <Button
                  type="button"
                  variant="outline"
                  class="sm:ml-auto"
                  data-test="close-search-filters-button"
                >
                  Cancel
                </Button>
              </DialogClose>
              <DialogClose as-child>
                <Button
                  type="button"
                  data-test="apply-search-filters-button"
                  @click="submitSearch"
                >
                  Apply
                </Button>
              </DialogClose>
            </DialogFooter>
          </DialogContent>
        </Tooltip>
      </Dialog>
      <Popover
        @close="nextTick(() => !searchForm.processing && searchForm.reset())"
      >
        <Tooltip>
          <TooltipTrigger as-child>
            <PopoverTrigger as-child>
              <Button
                type="button"
                variant="outline"
                size="icon"
                aria-label="Sort"
              >
                <IconArrowsDownUp />
              </Button>
            </PopoverTrigger>
          </TooltipTrigger>
          <TooltipContent>Sort</TooltipContent>
          <PopoverContent class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
              <h2 class="text-lg leading-none font-semibold">Sort</h2>
              <p class="text-sm text-muted-foreground">
                Use these options to sort your results.
              </p>
            </div>
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
            <div class="flex">
              <PopoverClose as-child>
                <Button
                  type="button"
                  variant="outline"
                  @click="(clearSortFilters(), nextTick(submitSearch))"
                >
                  Reset
                </Button>
              </PopoverClose>
              <PopoverClose as-child>
                <Button
                  type="button"
                  class="ml-auto"
                  @click="submitSearch"
                >
                  Apply
                </Button>
              </PopoverClose>
            </div>
          </PopoverContent>
        </Tooltip>
      </Popover>
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
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import type { PokemonBoxIndexPageProps } from '@/pages/Pokemon/Box/Index.vue';
import { PokemonTrait, SearchBoxSortBy, SortDirection } from '@/types';
import { route } from '@/types/helpers/route.js';
import AppCheckbox from './AppCheckbox.vue';
import AppIndicator from './AppIndicator.vue';
import AppInput from './AppInput.vue';
import AppSelect from './AppSelect.vue';
import { Button } from './ui/button';
import { Popover, PopoverContent, PopoverTrigger } from './ui/popover';
import { Tooltip, TooltipContent, TooltipTrigger } from './ui/tooltip';

const props = defineProps<{
  activeFilters: PokemonBoxIndexPageProps['activeFilters'];
  filterOptions: PokemonBoxIndexPageProps['filterOptions'];
}>();

const searchForm = useForm({
  search: props.activeFilters.search,
  types: props.activeFilters.types,
  traits: props.activeFilters.traits,
  generations: props.activeFilters.generations,
  sort: props.activeFilters.sort,
  dir: props.activeFilters.dir,
});

const activeFiltersCount = computed(
  () =>
    props.activeFilters.generations.length +
    props.activeFilters.traits.length +
    props.activeFilters.types.length,
);

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
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

function clearDialogFilters() {
  searchForm.traits = [];
  searchForm.generations = [];
  searchForm.types = [];
}

function clearSortFilters() {
  searchForm.sort = SearchBoxSortBy.CAPTURE_DATE;
  searchForm.dir = SortDirection.DESC;
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
    .get(route('web.pokemon.box.index'), {
      preserveScroll: true,
      preserveState: true,
      only: ['pokemonList', 'activeFilters'],
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
