<template>
  <Form :action="route('web.pokemon.index')">
    <Card class="p-4">
      <AppInput
        v-model="search"
        type="search"
        name="search"
        placeholder="Search by name..."
        :icon="IconSearch"
      />
    </Card>
  </Form>
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
import { Form, router } from '@inertiajs/vue3';
import { IconPokeball, IconSearch } from '@tabler/icons-vue';
import { watchDebounced } from '@vueuse/core';
import { ref } from 'vue';
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

const search = ref(
  new URLSearchParams(window.location.search).get('search') || '',
);

watchDebounced(
  search,
  () => {
    router.get(
      route('web.pokemon.index'),
      { search: search.value || undefined },
      { preserveState: true, preserveScroll: true, only: ['pokemonInBox'] },
    );
  },
  {
    debounce: 250,
  },
);
</script>
