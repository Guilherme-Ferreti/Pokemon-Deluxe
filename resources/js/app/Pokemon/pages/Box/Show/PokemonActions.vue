<template>
  <div
    class="flex flex-wrap items-center gap-2"
    data-test="pokemon-actions"
  >
    <PokemonEvolveDialog />
    <PokemonBoostDialog />
    <PokemonStatsDialog />
    <PokemonRemoveFromPartyButton v-if="page.props.pokemon.inParty" />
    <PokemonAddToPartyButton v-else />
    <PokemonLockDialog />
    <PokemonReleaseDialog />
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';
import PokemonAddToPartyButton from './PokemonAddToPartyButton.vue';
import PokemonBoostDialog from './PokemonBoostDialog.vue';
import PokemonEvolveDialog from './PokemonEvolveDialog.vue';
import PokemonLockDialog from './PokemonLockDialog.vue';
import PokemonReleaseDialog from './PokemonReleaseDialog.vue';
import PokemonRemoveFromPartyButton from './PokemonRemoveFromPartyButton.vue';
import PokemonStatsDialog from './PokemonStatsDialog.vue';

const page = usePage<BoxShowPageProps>();
</script>
