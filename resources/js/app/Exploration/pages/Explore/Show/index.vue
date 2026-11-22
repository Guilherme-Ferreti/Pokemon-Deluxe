<template>
  <Head :title="page.props.area.label" />
  <div class="flex flex-col gap-1.5">
    <AppPageHeader :title="page.props.area.label">
      <AreaSpeciesDialog />
    </AppPageHeader>
    <PokemonTypeBadges :types="page.props.area.types" />
  </div>
  <BattleInProgressEmptyState v-if="page.props.isBattleInProgress" />
  <EncounterCard v-else-if="page.props.encounter" />
  <EncounterEmptyState v-else />
</template>

<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import { route } from '@/shared/types/helpers/route';
import type { ExploreShowPageProps } from '../../../types/page-props/ExploreShowPageProps';
import AreaSpeciesDialog from './AreaSpeciesDialog.vue';
import BattleInProgressEmptyState from './BattleInProgressEmptyState.vue';
import EncounterCard from './EncounterCard.vue';
import EncounterEmptyState from './EncounterEmptyState.vue';

const page = usePage<ExploreShowPageProps>();

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Explore',
      href: route('exploration.web.index'),
    },
    {
      name: page.props.area.label,
    },
  ],
});
</script>
