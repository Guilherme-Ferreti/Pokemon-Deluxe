<template>
  <template v-if="page.props.pokemonList.data.length > 0">
    <ul class="grid grid-cols-[repeat(auto-fill,minmax(16.25rem,1fr))] gap-4">
      <PokemonCard
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
import AppEmptyState from '@/shared/components/content/AppEmptyState.vue';
import AppPagination from '@/shared/components/list/AppPagination.vue';
import type { BoxIndexPageProps } from '../../../types/page-props/BoxIndexPageProps';
import PokemonCard from './PokemonCard.vue';

const page = usePage<BoxIndexPageProps>();
</script>
