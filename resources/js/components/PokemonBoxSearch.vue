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
        <div class="grid grid-cols-[auto_1fr] place-items-center gap-8">
          <p class="text-sm font-semibold">By characteristic:</p>
          <div class="grid grid-cols-3 gap-4">
            <AppCheckbox
              id="shiny"
              v-model="searchForm.isShiny"
              label="Shiny"
              name="shiny"
              class="w-fit"
            />
            <AppCheckbox
              id="baby"
              v-model="searchForm.isBaby"
              label="Baby"
              name="baby"
              class="w-fit"
            />
            <AppCheckbox
              id="legendary"
              v-model="searchForm.isLegendary"
              label="Legendary"
              name="legendary"
              class="w-fit"
            />
            <AppCheckbox
              id="mythical"
              v-model="searchForm.isMythical"
              label="Mythical"
              name="mythical"
              class="w-fit"
            />
            <AppCheckbox
              id="mega"
              v-model="searchForm.isMega"
              label="Mega"
              name="mega"
              class="w-fit"
            />
            <AppCheckbox
              id="gmax"
              v-model="searchForm.isGmax"
              label="Gmax"
              name="gmax"
              class="w-fit"
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
            class="ml-auto"
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
  isShiny: false,
  isBaby: false,
  isLegendary: false,
  isMythical: false,
  isMega: false,
  isGmax: false,
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
  const urlSearchParams = new URLSearchParams(window.location.search);

  searchForm.search = urlSearchParams.get('search') || '';

  searchForm.isShiny = urlSearchParams.get('shiny') === 'true';
  searchForm.isBaby = urlSearchParams.get('baby') === 'true';
  searchForm.isLegendary = urlSearchParams.get('legendary') === 'true';
  searchForm.isMythical = urlSearchParams.get('mythical') === 'true';
  searchForm.isMega = urlSearchParams.get('mega') === 'true';
  searchForm.isGmax = urlSearchParams.get('gmax') === 'true';

  searchForm.defaults();
}

function clearSearchAndDialogFilters() {
  searchForm.search = '';
  searchForm.isShiny = false;
  searchForm.isBaby = false;
  searchForm.isLegendary = false;
  searchForm.isMythical = false;
  searchForm.isMega = false;
  searchForm.isGmax = false;
}

function submitSearch() {
  if (!searchForm.isDirty) {
    return;
  }

  searchForm
    .transform((data) => ({
      search: data.search || undefined,
      shiny: data.isShiny || undefined,
      baby: data.isBaby || undefined,
      legendary: data.isLegendary || undefined,
      mythical: data.isMythical || undefined,
      mega: data.isMega || undefined,
      gmax: data.isGmax || undefined,
    }))
    .get(route('web.pokemon.index'), {
      preserveScroll: true,
      preserveState: true,
      only: ['pokemonInBox'],
    });
}
</script>
