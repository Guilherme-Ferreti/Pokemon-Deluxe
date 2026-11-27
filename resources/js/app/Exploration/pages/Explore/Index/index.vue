<template>
  <Head title="Explore" />
  <AppPageHeader
    title="Explore"
    description="Pick an area to look for wild Pokémon."
  />
  <BattleInProgressNotice v-if="page.props.isBattleInProgress" />
  <div class="grid grid-cols-[repeat(auto-fill,minmax(16rem,1fr))] gap-4">
    <AreaCard
      v-for="area in page.props.areas"
      :key="area.slug"
      :area="area"
    />
  </div>
</template>

<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import { route } from '@/shared/types/helpers/route';
import type { ExploreIndexPageProps } from '../../../types/page-props/ExploreIndexPageProps';
import AreaCard from './AreaCard.vue';
import BattleInProgressNotice from './BattleInProgressNotice.vue';

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Explore',
      href: route('exploration.web.index'),
    },
    {
      name: 'List',
    },
  ],
});

const page = usePage<ExploreIndexPageProps>();
</script>
