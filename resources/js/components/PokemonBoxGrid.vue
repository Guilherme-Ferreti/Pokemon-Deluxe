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
    </Card>
  </form>
  <template v-if="pokemonList.data.length > 0">
    <div class="grid grid-cols-[repeat(auto-fill,minmax(16.25rem,1fr))] gap-4">
      <PokemonBoxCard
        v-for="pokemon in pokemonList.data"
        :key="pokemon.id"
        :pokemon="pokemon"
      />
    </div>
    <AppPagination
      :list="pokemonList"
      :only="['pokemonInBox']"
    />
  </template>
  <AppEmptyState
    v-else
    title="No Pokémon in your box"
    description="Catch some Pokémon to see them here."
    :icon="IconPokeball"
  />
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { IconPokeball, IconSearch } from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { Card } from '@/components/ui/card';
import { route } from '@/types/helpers/route.js';
import type { PaginatedList } from '@/types/index.js';
import type { ElementalType } from '@/types/pokemon.js';
import AppEmptyState from './AppEmptyState.vue';
import AppInput from './AppInput.vue';
import AppPagination from './AppPagination.vue';
import PokemonBoxCard from './PokemonBoxCard.vue';

defineProps<{
  pokemonList: PaginatedList<{
    id: string;
    name: string;
    frontSprite?: string;
    level: number;
    experience: number;
    pokedex: {
      primaryType: ElementalType;
      secondaryType: ElementalType | null;
    };
    moves: {
      name: string;
    }[];
  }>;
}>();

const searchForm = useForm({
  search: new URLSearchParams(window.location.search).get('search') || '',
});

watchDebounced(
  () => searchForm.search,
  () => submitSearch(),
  { debounce: 450 },
);

function submitSearch() {
  if (!searchForm.isDirty) {
    return;
  }

  searchForm
    .transform((data) => ({
      search: data.search || undefined,
    }))
    .get(route('web.pokemon.index'), {
      preserveScroll: true,
      preserveState: true,
      only: ['pokemonInBox'],
    });
}
</script>
