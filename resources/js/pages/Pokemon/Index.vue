<template>
  <Head title="Your Pokemon" />
  <AppSection aria-labelledby="your-pokemon__heading">
    <AppSectionHeading
      id="your-pokemon__heading"
      title="Your Pokemon"
      description="Manage your party."
      :icon="IconPokeball"
    />
    <PartyCard :pokemon-in-party="pokemonInParty" />
  </AppSection>
  <AppSection aria-labelledby="box__heading">
    <AppSectionHeading
      id="box__heading"
      title="Box"
      description="Manage your captured Pokemon."
      :icon="IconArchive"
    />
    <PokemonBoxGrid
      :pokemon-in-box="pokemonInBox"
      :search-filters="searchFilters"
    />
  </AppSection>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { IconArchive, IconPokeball } from '@tabler/icons-vue';
import AppSection from '@/components/AppSection.vue';
import AppSectionHeading from '@/components/AppSectionHeading.vue';
import PartyCard from '@/components/PartyCard.vue';
import PokemonBoxGrid from '@/components/PokemonBoxGrid.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import type {
  ElementalType,
  Generation,
  PaginatedList,
  PokemonCharacteristic,
} from '@/types';
import { route } from '@/types/helpers/route';

export interface PokemonIndexPageProps {
  pokemonInParty: {
    id: string;
    name: string;
    frontSprite?: string;
    level: number;
  }[];
  pokemonInBox: PaginatedList<{
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
  searchFilters: {
    search: string;
    elementalTypes: ElementalType[];
    characteristics: PokemonCharacteristic[];
    generations: Generation[];
    sortBy: 'level' | 'experience';
    sortDirection: 'asc' | 'desc';
  };
}

defineProps<PokemonIndexPageProps>();

const { setBreadcrumbs } = useBreadcrumbs();
setBreadcrumbs([
  { title: 'Pokemon', href: route('web.pokemon.index') },
  { title: 'List' },
]);
</script>
