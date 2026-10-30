<template>
  <PokemonBoxSearchBar />
  <template v-if="page.props.pokemonList.data.length > 0">
    <ul class="grid grid-cols-[repeat(auto-fill,minmax(16.25rem,1fr))] gap-4">
      <PokemonBoxCard
        v-for="pokemon in page.props.pokemonList.data"
        :key="pokemon.id"
        :pokemon="pokemon"
      />
    </ul>
    <AppPagination
      :list="page.props.pokemonList"
      :only="['pokemonList']"
      preserve-scroll
    />
  </template>
  <AppEmptyState
    v-else
    title="No Pokémon in your box"
    description="Catch some Pokémon to see them here."
    data-test="pokemon-box__empty-state"
    :icon="IconPokeball"
  />
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconPokeball } from '@tabler/icons-vue';
import PokemonBoxCard from '@/app/Pokemon/components/PokemonBoxCard.vue';
import PokemonBoxSearchBar from '@/app/Pokemon/components/PokemonBoxSearchBar.vue';
import type { BoxIndexPageProps } from '@/app/Pokemon/types/page-props/BoxIndexPageProps';
import AppEmptyState from '@/shared/components/AppEmptyState.vue';
import AppPagination from '@/shared/components/list/AppPagination.vue';

const page = usePage<BoxIndexPageProps>();
</script>
