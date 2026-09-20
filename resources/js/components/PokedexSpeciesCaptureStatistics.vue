<template>
  <AppInfoCard
    title="Capture Statistics"
    data-test="pokedex-species-capture-statistics"
    :description="`Total number of ${speciesName} in accounts across the game`"
  >
    <AppInfoCardSectionTitle
      title="Global"
      icon-tooltip-text="Global numbers are updated once per day"
      :icon="IconInfoCircle"
    />
    <AppInfoCardRow
      title="Regular"
      :text="statistics.global.regular.toString()"
    />
    <AppInfoCardRow
      title="Shiny"
      :text="statistics.global.shiny.toString()"
    />
    <Separator />
    <AppInfoCardSectionTitle title="Your account" />
    <AppInfoCardRow
      title="Regular"
      :text="statistics.currentUser.regular.toString()"
    />
    <AppInfoCardRow
      title="Shiny"
      :text="statistics.currentUser.shiny.toString()"
    />
  </AppInfoCard>
</template>

<script setup lang="ts">
import { IconInfoCircle } from '@tabler/icons-vue';
import AppInfoCard from '@/components/AppInfoCard.vue';
import AppInfoCardRow from '@/components/AppInfoCardRow.vue';
import AppInfoCardSectionTitle from '@/components/AppInfoCardSectionTitle.vue';
import { Separator } from '@/components/ui/separator';
import type { DexPokedexShowPageProps } from '@/types';

defineProps<{
  speciesName: string;
  statistics: DexPokedexShowPageProps['captureStatistics'];
}>();
</script>
