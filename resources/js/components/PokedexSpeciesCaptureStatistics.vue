<template>
  <AppInfoCard
    title="Capture Statistics"
    :description="`Total number of ${speciesName} in accounts across the game`"
  >
    <span class="flex items-center gap-2 text-sm font-bold">
      <span>Global</span>
      <Tooltip>
        <TooltipTrigger as-child>
          <IconInfoCircle size="14" />
        </TooltipTrigger>
        <TooltipContent>Global numbers are updated once per day</TooltipContent>
      </Tooltip>
    </span>
    <AppInfoCardRow
      title="Regular"
      :text="statistics.global.regular.toString()"
    />
    <AppInfoCardRow
      title="Shiny"
      :text="statistics.global.shiny.toString()"
    />
    <Separator />
    <span class="text-sm font-bold"> Your account</span>
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
import { Separator } from '@/components/ui/separator';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip';
import type { DexPokedexShowPageProps } from '@/pages/Dex/Pokedex/Show.vue';

defineProps<{
  speciesName: string;
  statistics: DexPokedexShowPageProps['captureStatistics'];
}>();
</script>
