<template>
  <Combobox :ignore-filter="true">
    <ComboboxAnchor as-child>
      <ComboboxInput as-child>
        <InputGroup class="w-full bg-background shadow-xs">
          <InputGroupInput
            v-model="http.search"
            name="search"
            type="search"
            placeholder="Search a pokemon..."
            autocomplete="off"
            @input="handleTyping"
          />
          <InputGroupAddon>
            <IconSearch />
          </InputGroupAddon>
        </InputGroup>
      </ComboboxInput>
    </ComboboxAnchor>
    <ComboboxList class="w-(--reka-popper-anchor-width)">
      <ul
        v-if="isTyping || http.processing"
        class="flex flex-col"
      >
        <li
          v-for="i in 5"
          :key="i"
          class="flex items-center gap-2 px-2 py-1.5"
        >
          <Skeleton class="size-8 shrink-0 rounded-sm" />
          <div class="flex flex-col gap-1.5">
            <Skeleton class="h-3 w-10" />
            <Skeleton class="h-4 w-30" />
          </div>
        </li>
      </ul>
      <ComboboxEmpty
        v-else-if="autocompleteOptions.length === 0"
        data-test="pokedex-species-autocomplete__empty-state"
      >
        No Pokémon found.
      </ComboboxEmpty>
      <ComboboxGroup v-else>
        <ComboboxItem
          v-for="option in autocompleteOptions"
          :key="option.name"
          :value="option"
          @select="() => handleOptionSelect(option)"
        >
          <AppSpeciesPortrait
            :url="option.portrait"
            :name="option.name"
          />
          <div class="flex flex-col justify-between">
            <span class="text-[9px] font-black tracking-widest text-slate-400">
              #{{ formatPokedexNumber(option.pokedexNumber) }}
            </span>
            <span class="text-sm font-medium">{{ option.name }}</span>
          </div>
        </ComboboxItem>
      </ComboboxGroup>
    </ComboboxList>
  </Combobox>
</template>

<script setup lang="ts">
import { router, useHttp } from '@inertiajs/vue3';
import { IconSearch } from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { ComboboxInput } from 'reka-ui';
import { ref } from 'vue';
import AppSpeciesPortrait from '@/shared/components/AppSpeciesPortrait.vue';
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxItem,
  ComboboxList,
} from '@/shared/components/ui/combobox';
import InputGroup from '@/shared/components/ui/input-group/InputGroup.vue';
import InputGroupAddon from '@/shared/components/ui/input-group/InputGroupAddon.vue';
import InputGroupInput from '@/shared/components/ui/input-group/InputGroupInput.vue';
import { Skeleton } from '@/shared/components/ui/skeleton';
import { formatPokedexNumber } from '@/shared/lib/utils.js';
import { route } from '@/shared/types/helpers/route';

interface AutocompleOption {
  name: string;
  pokedexNumber: number;
  portrait: string | null;
  slug: string;
}

const autocompleteOptions = ref<AutocompleOption[]>([]);
const isTyping = ref(false);

let typingTimeout: number;

const http = useHttp({
  search: '',
});

const unwatchSearch = watchDebounced(
  () => http.search,
  () => submitSearch(),
  { debounce: 450 },
);

function handleTyping() {
  isTyping.value = true;

  clearTimeout(typingTimeout);

  typingTimeout = setTimeout(() => (isTyping.value = false), 450);
}

function submitSearch() {
  if (http.search.length === 0) {
    autocompleteOptions.value = [];

    return;
  }

  http.get(route('dex.api.species.autocomplete'), {
    onSuccess: (response) => {
      autocompleteOptions.value = response as AutocompleOption[];
    },
  });
}

function handleOptionSelect(option: AutocompleOption) {
  unwatchSearch();

  http.search = option.name;

  router.get(route('dex.web.pokedex.show', { species: option.slug }));
}
</script>
