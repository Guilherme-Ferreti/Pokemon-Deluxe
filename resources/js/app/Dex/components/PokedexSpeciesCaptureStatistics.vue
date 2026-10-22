<template>
  <AppInfoCard
    title="Capture Statistics"
    data-test="pokedex-species-capture-statistics"
    :description="`Total number of ${page.props.species.name} in accounts across the game`"
  >
    <AppInfoCardSectionTitle
      title="Global"
      icon-tooltip-text="Global numbers are updated once per day"
      :icon="IconInfoCircle"
    />
    <AppInfoCardRow
      title="Regular"
      :text="page.props.captureStatistics.global.regular.toString()"
    />
    <AppInfoCardRow
      title="Shiny"
      :text="page.props.captureStatistics.global.shiny.toString()"
    />
    <Separator />
    <AppInfoCardSectionTitle title="Your account" />
    <AppInfoCardRow
      title="Regular"
      :text="page.props.captureStatistics.currentUser.regular.toString()"
    />
    <AppInfoCardRow
      title="Shiny"
      :text="page.props.captureStatistics.currentUser.shiny.toString()"
    />
  </AppInfoCard>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconInfoCircle } from '@tabler/icons-vue';
import type { PokedexShowPageProps } from '@/app/Dex/types/page-props/PokedexShowPageProps';
import AppInfoCard from '@/shared/components/AppInfoCard.vue';
import AppInfoCardRow from '@/shared/components/AppInfoCardRow.vue';
import AppInfoCardSectionTitle from '@/shared/components/AppInfoCardSectionTitle.vue';
import { Separator } from '@/shared/components/ui/separator';

const page = usePage<PokedexShowPageProps>();
</script>
