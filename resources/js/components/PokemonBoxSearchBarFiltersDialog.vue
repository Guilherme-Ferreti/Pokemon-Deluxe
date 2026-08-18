<template>
  <Dialog @close="$emit('close')">
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
                v-for="option in typeOptions"
                :key="option.value"
                v-model="selectedTypes"
                name="types[]"
                :label="option.label"
                :value="option.value"
                :disabled="
                  selectedTypes.length >= 2 &&
                  !selectedTypes.includes(option.value)
                "
              />
            </div>
          </div>
          <div class="dialog-filters__filters-group">
            <p class="dialog-filters__filters-group-title">By trait:</p>
            <div class="dialog-filters__filters-group-checkboxes">
              <AppCheckbox
                v-for="option in traitOptions"
                :key="option.value"
                v-model="selectedTraits"
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
                v-for="option in generationOptions"
                :key="option.value"
                v-model="selectedGenerations"
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
              @click="(clearFilters(), nextTick(() => $emit('submit')))"
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
              @click="$emit('submit')"
            >
              Apply
            </Button>
          </DialogClose>
        </DialogFooter>
      </DialogContent>
    </Tooltip>
  </Dialog>
</template>

<script setup lang="ts">
import { IconFilter2 } from '@tabler/icons-vue';
import { nextTick } from 'vue';
import AppCheckbox from '@/components/AppCheckbox.vue';
import AppIndicator from '@/components/AppIndicator.vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogFooter,
  DialogTitle,
  DialogDescription,
  DialogClose,
} from '@/components/ui/dialog';
import {
  Tooltip,
  TooltipTrigger,
  TooltipContent,
} from '@/components/ui/tooltip';
import type { PokemonBoxIndexPageProps } from '@/pages/Pokemon/Box/Index.vue';
import { PokemonTrait } from '@/types/php-types.js';

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
  submit: [value: void];
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

function clearFilters() {
  selectedTraits.value = [];
  selectedTypes.value = [];
  selectedGenerations.value = [];
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
