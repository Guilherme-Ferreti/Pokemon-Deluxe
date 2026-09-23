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
import type { PokedexShowPageProps } from '@/app/Dex/types/page-props/PokedexShowPageProps';
import AppInfoCard from '@/shared/components/AppInfoCard.vue';
import AppInfoCardRow from '@/shared/components/AppInfoCardRow.vue';
import AppInfoCardSectionTitle from '@/shared/components/AppInfoCardSectionTitle.vue';
import { Separator } from '@/shared/components/ui/separator';
import type { Species } from '@/shared/types';

defineProps<{
  speciesName: Species['name'];
  statistics: PokedexShowPageProps['captureStatistics'];
}>();
</script>
