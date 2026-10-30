<template>
  <Head title="Pokédex" />
  <AppPageHeader
    title="Pokédex"
    description="Below is a list of all Pokémon in the game."
  />
  <p>
    The <InBoxIcon /> icon indicates pokémon you have in your box.
    <br />
    The <NotInBoxIcon /> icon indicates pokémon you do not have in your box.
  </p>
  <AppSection>
    <Pokedex />
  </AppSection>
</template>

<script lang="ts" setup>
import { Head, setLayoutProps } from '@inertiajs/vue3';
import Pokedex from '@/app/Dex/components/Pokedex.vue';
import AppSection from '@/shared/components/content/AppSection.vue';
import InBoxIcon from '@/shared/components/game/InBoxIcon.vue';
import NotInBoxIcon from '@/shared/components/game/NotInBoxIcon.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import { route } from '@/shared/types/helpers/route';

setLayoutProps({
  pageSize: 'small',
  breadcrumbs: [
    {
      name: 'Pokédex',
      href: route('dex.web.pokedex.index'),
    },
    {
      name: 'List',
    },
  ],
});
</script>
