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
      >
        <template #trigger>
          <AppTooltip text="Filters">
            <Button
              type="button"
              variant="outline"
              size="icon"
              aria-label="Filters"
            >
              <IconFilter2 />
            </Button>
          </AppTooltip>
        </template>
        <div class="dialog-filters__filters-group">
          <p class="dialog-filters__filters-group-title">By characteristic:</p>
          <div class="dialog-filters__filters-group-checkboxes">
            <AppCheckbox
              v-for="characteristic in characteristicOptions"
              :key="characteristic.label"
              v-model="searchForm.characteristics"
              name="characteristics[]"
              :label="characteristic.label"
              :value="characteristic.value"
            />
          </div>
        </div>
        <div class="dialog-filters__filters-group">
          <p class="dialog-filters__filters-group-title">By generation:</p>
          <div class="dialog-filters__filters-group-checkboxes">
            <AppCheckbox
              v-for="generation in generationOptions"
              :key="generation.label"
              v-model="searchForm.generations"
              name="generations[]"
              :label="generation.label"
              :value="generation.value"
            />
          </div>
        </div>
        <template #footer>
          <DialogClose as-child>
            <Button
              type="button"
              @click="submitSearch"
            >
              Apply
            </Button>
          </DialogClose>
          <DialogClose as-child>
            <Button
              type="button"
              variant="outline"
              @click="(clearSearchAndDialogFilters(), nextTick(submitSearch))"
            >
              Clear all
            </Button>
          </DialogClose>
          <DialogClose
            as-child
            class="sm:ml-auto"
          >
            <Button
              type="button"
              variant="outline"
              @click="() => searchForm.reset()"
            >
              Cancel
            </Button>
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
import { nextTick } from 'vue';
import { Card } from '@/components/ui/card';
import type { PokemonIndexPageProps } from '@/pages/Pokemon/Index.vue';
import { route } from '@/types/helpers/route.js';
import { Generation, PokemonCharacteristic } from '@/types/pokemon.js';
import AppCheckbox from './AppCheckbox.vue';
import AppDialog from './AppDialog.vue';
import AppInput from './AppInput.vue';
import AppTooltip from './AppTooltip.vue';
import { Button } from './ui/button';
import DialogClose from './ui/dialog/DialogClose.vue';

const props = defineProps<{
  searchFilters: PokemonIndexPageProps['searchFilters'];
}>();

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
  characteristics: props.searchFilters.characteristics,
  generations: props.searchFilters.generations,
});

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
);

function clearSearchAndDialogFilters() {
  searchForm.search = '';
  searchForm.characteristics = [];
  searchForm.generations = [];
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

.dialog-filters__filters-group {
  @apply grid gap-4 sm:grid-cols-[1fr_3fr] sm:gap-8;
}

.dialog-filters__filters-group-title {
  @apply text-sm font-semibold;
}

.dialog-filters__filters-group-checkboxes {
  @apply grid grid-cols-2 gap-4 sm:grid-cols-3;
}
</style>
