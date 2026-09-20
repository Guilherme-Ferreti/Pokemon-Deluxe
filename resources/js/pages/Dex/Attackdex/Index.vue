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
import AppPageHeader from '@/components/AppPageHeader.vue';
import AppSection from '@/components/AppSection.vue';
import Attackdex from '@/components/Attackdex.vue';
import type { DexAttackdexIndexPageProps } from '@/types';
import { route } from '@/types/helpers/route';

defineProps<DexAttackdexIndexPageProps>();

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
