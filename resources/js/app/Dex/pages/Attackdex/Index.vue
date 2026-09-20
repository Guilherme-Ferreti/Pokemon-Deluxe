<template>
  <Head title="Attackdex" />
  <AppPageHeader
    title="Attackdex"
    description="Below is a list of all moves in the game."
  />
  <p>
    Moves marked with <span class="font-extrabold">Z</span> icon are Z-Moves.
    <br />
    Moves marked with <span class="font-extrabold">D</span> icon are Dynamax
    moves.
  </p>
  <AppSection>
    <Attackdex
      :moves="moves"
      :active-filters="activeFilters"
      :filter-options="filterOptions"
    />
  </AppSection>
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import Attackdex from '@/app/Dex/components/Attackdex.vue';
import type { AttackdexIndexPageProps } from '@/app/Dex/types/AttackdexIndexPageProps';
import AppPageHeader from '@/shared/components/AppPageHeader.vue';
import AppSection from '@/shared/components/AppSection.vue';
import { route } from '@/shared/types/helpers/route';

defineProps<AttackdexIndexPageProps>();

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Attackdex',
      href: route('dex.web.attackdex.index'),
    },
    {
      name: 'List',
    },
  ],
});
</script>
