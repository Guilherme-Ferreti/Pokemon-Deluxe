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
    <Card class="gap-4 p-4">
      <SpeciesSearchForm ref="searchForm" />
      <SpeciesTable
        v-if="pokedexSearchFormRef"
        :search-form="pokedexSearchFormRef.searchForm"
      />
    </Card>
    <AppPagination :list="page.props.speciesList" />
  </AppSection>
</template>

<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import SpeciesSearchForm from '@/app/Dex/pages/Pokedex/Index/SpeciesSearchForm.vue';
import SpeciesTable from '@/app/Dex/pages/Pokedex/Index/SpeciesTable.vue';
import type { PokedexIndexPageProps } from '@/app/Dex/types/page-props/PokedexIndexPageProps';
import AppSection from '@/shared/components/content/AppSection.vue';
import InBoxIcon from '@/shared/components/game/InBoxIcon.vue';
import NotInBoxIcon from '@/shared/components/game/NotInBoxIcon.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import AppPagination from '@/shared/components/list/AppPagination.vue';
import { Card } from '@/shared/components/ui/card';
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

const page = usePage<PokedexIndexPageProps>();

const pokedexSearchFormRef = useTemplateRef('searchForm');
</script>
