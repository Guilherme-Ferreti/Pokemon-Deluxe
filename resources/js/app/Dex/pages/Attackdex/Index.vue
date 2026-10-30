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
    <Card class="gap-4 p-4">
      <AttackdexSearchForm ref="searchForm" />
      <AttackdexTable
        v-if="attackdexSearchFormRef"
        :search-form="attackdexSearchFormRef.searchForm"
      />
    </Card>
    <AppPagination :list="page.props.moves" />
  </AppSection>
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import AttackdexSearchForm from '@/app/Dex/components/AttackdexSearchForm.vue';
import AttackdexTable from '@/app/Dex/components/AttackdexTable.vue';
import type { AttackdexIndexPageProps } from '@/app/Dex/types/page-props/AttackdexIndexPageProps';
import AppPagination from '@/shared/components/AppPagination.vue';
import AppSection from '@/shared/components/AppSection.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import { Card } from '@/shared/components/ui/card';
import { route } from '@/shared/types/helpers/route';

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

const page = usePage<AttackdexIndexPageProps>();

const attackdexSearchFormRef = useTemplateRef('searchForm');
</script>
