<template>
  <Head title="Your Pokemon" />
  <AppSection class="space-y-6">
    <AppSectionHeader
      title="Your Pokemon"
      description="Manage your party."
      :icon="IconPokeball"
    />
    <PartyCard :pokemon-in-party="pokemonInParty" />
  </AppSection>
  <AppSection class="space-y-6">
    <AppSectionHeader
      title="Box"
      description="Manage your captured Pokemon."
      :icon="IconArchive"
    />
    <PokemonBoxGrid :pokemon-list="pokemonInBox" />
  </AppSection>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { IconArchive, IconPokeball } from '@tabler/icons-vue';
import AppSection from '@/components/AppSection.vue';
import AppSectionHeader from '@/components/AppSectionHeader.vue';
import PartyCard from '@/components/PartyCard.vue';
import PokemonBoxGrid from '@/components/PokemonBoxGrid.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import type { ElementalType } from '@/types';
import { route } from '@/types/helpers/route';

defineProps<{
  pokemonInParty: {
    id: string;
    name: string;
    frontSprite?: string;
    level: number;
  }[];
  pokemonInBox: {
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
  }[];
}>();

const { setBreadcrumbs } = useBreadcrumbs();
setBreadcrumbs([
  { title: 'Pokemon', href: route('web.pokemon.index') },
  { title: 'List' },
]);
</script>
