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
              id="shiny"
              v-model="searchForm.shiny"
              label="Shiny"
              name="shiny"
            />
            <AppCheckbox
              id="baby"
              v-model="searchForm.baby"
              label="Baby"
              name="baby"
            />
            <AppCheckbox
              id="legendary"
              v-model="searchForm.legendary"
              label="Legendary"
              name="legendary"
            />
            <AppCheckbox
              id="mythical"
              v-model="searchForm.mythical"
              label="Mythical"
              name="mythical"
            />
            <AppCheckbox
              id="mega"
              v-model="searchForm.mega"
              label="Mega"
              name="mega"
            />
            <AppCheckbox
              id="gmax"
              v-model="searchForm.gmax"
              label="Gmax"
              name="gmax"
            />
          </div>
        </div>
        <div class="dialog-filters__filters-group">
          <p class="dialog-filters__filters-group-title">By generation:</p>
          <div class="dialog-filters__filters-group-checkboxes">
            <AppCheckbox
              id="kanto"
              v-model="searchForm.kanto"
              label="Kanto"
              name="kanto"
            />
            <AppCheckbox
              id="johto"
              v-model="searchForm.johto"
              label="Johto"
              name="johto"
            />
            <AppCheckbox
              id="hoenn"
              v-model="searchForm.hoenn"
              label="Hoenn"
              name="hoenn"
            />
            <AppCheckbox
              id="sinnoh"
              v-model="searchForm.sinnoh"
              label="Sinnoh"
              name="sinnoh"
            />
            <AppCheckbox
              id="unova"
              v-model="searchForm.unova"
              label="Unova"
              name="unova"
            />
            <AppCheckbox
              id="kalos"
              v-model="searchForm.kalos"
              label="Kalos"
              name="kalos"
            />
            <AppCheckbox
              id="alola"
              v-model="searchForm.alola"
              label="Alola"
              name="alola"
            />
            <AppCheckbox
              id="galar"
              v-model="searchForm.galar"
              label="Galar"
              name="galar"
            />
            <AppCheckbox
              id="paldea"
              v-model="searchForm.paldea"
              label="Paldea"
              name="paldea"
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
              @click="
                () => {
                  clearSearchAndDialogFilters();
                  submitSearch();
                }
              "
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
              Close
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
import { onMounted } from 'vue';
import { Card } from '@/components/ui/card';
import { route } from '@/types/helpers/route.js';
import AppCheckbox from './AppCheckbox.vue';
import AppDialog from './AppDialog.vue';
import AppInput from './AppInput.vue';
import AppTooltip from './AppTooltip.vue';
import { Button } from './ui/button';
import DialogClose from './ui/dialog/DialogClose.vue';

const searchForm = useForm({
  search: '',

  shiny: false,
  baby: false,
  legendary: false,
  mythical: false,
  mega: false,
  gmax: false,

  kanto: false,
  johto: false,
  hoenn: false,
  sinnoh: false,
  unova: false,
  kalos: false,
  alola: false,
  galar: false,
  paldea: false,
});

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
);

onMounted(() => {
  fillSearchAndDialogFiltersWithUrlParams();
});

function fillSearchAndDialogFiltersWithUrlParams() {
  const params = new URLSearchParams(window.location.search);

  searchForm.search = params.get('search') || '';

  searchForm.shiny = params.get('shiny') === 'true';
  searchForm.baby = params.get('baby') === 'true';
  searchForm.legendary = params.get('legendary') === 'true';
  searchForm.mythical = params.get('mythical') === 'true';
  searchForm.mega = params.get('mega') === 'true';
  searchForm.gmax = params.get('gmax') === 'true';

  searchForm.kanto = params.get('kanto') === 'true';
  searchForm.johto = params.get('johto') === 'true';
  searchForm.hoenn = params.get('hoenn') === 'true';
  searchForm.sinnoh = params.get('sinnoh') === 'true';
  searchForm.unova = params.get('unova') === 'true';
  searchForm.kalos = params.get('kalos') === 'true';
  searchForm.alola = params.get('alola') === 'true';
  searchForm.galar = params.get('galar') === 'true';
  searchForm.paldea = params.get('paldea') === 'true';

  searchForm.defaults();
}

function clearSearchAndDialogFilters() {
  searchForm.search = '';

  searchForm.shiny = false;
  searchForm.baby = false;
  searchForm.legendary = false;
  searchForm.mythical = false;
  searchForm.mega = false;
  searchForm.gmax = false;

  searchForm.kanto = false;
  searchForm.johto = false;
  searchForm.hoenn = false;
  searchForm.sinnoh = false;
  searchForm.unova = false;
  searchForm.kalos = false;
  searchForm.alola = false;
  searchForm.galar = false;
  searchForm.paldea = false;
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
      only: ['pokemonInBox'],
    });
}
</script>

<style scoped>
@reference '../../css/app.css';

.dialog-filters__filters-group {
  @apply grid gap-4 sm:grid-cols-[auto_1fr] sm:gap-8;
}

.dialog-filters__filters-group-title {
  @apply text-sm font-semibold;
}

.dialog-filters__filters-group-checkboxes {
  @apply grid grid-cols-2 gap-4 sm:grid-cols-3;

  > {
    @apply w-fit;
  }
}
</style>
