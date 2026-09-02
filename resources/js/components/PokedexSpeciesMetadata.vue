<template>
  <AppInfoCard title="Pokédex Data">
    <AppInfoCardRow title="Regular/Shiny">
      <div class="flex gap-2">
        <Tooltip>
          <TooltipTrigger>
            <AppSpeciesPortrait
              v-if="species.portraits.regular"
              :url="species.portraits.regular"
              :name="species.name"
            />
          </TooltipTrigger>
          <TooltipContent>Regular</TooltipContent>
        </Tooltip>
        <Tooltip>
          <TooltipTrigger>
            <AppSpeciesPortrait
              v-if="species.portraits.shiny"
              :url="species.portraits.shiny"
              :name="species.name"
            />
          </TooltipTrigger>
          <TooltipContent>Shiny</TooltipContent>
        </Tooltip>
      </div>
    </AppInfoCardRow>
    <AppInfoCardRow title="Type(s)">
      <ElementalTypeBadges :types="species.types" />
    </AppInfoCardRow>
    <AppInfoCardRow
      title="Generation"
      :text="species.generation"
    />
    <AppInfoCardRow
      v-if="Object.values(species.traits).some(Boolean)"
      title="Traits"
    >
      <div class="flex flex-wrap gap-2">
        <Badge
          v-if="species.traits.isBaby"
          variant="outline"
          class="text-xs font-medium"
        >
          Baby
        </Badge>
        <Badge
          v-if="species.traits.isLegendary"
          variant="outline"
          class="text-xs font-medium"
        >
          Legendary
        </Badge>
        <Badge
          v-if="species.traits.isMythical"
          variant="outline"
          class="text-xs font-medium"
        >
          Mythical
        </Badge>
        <Badge
          v-if="species.traits.isMega"
          variant="outline"
          class="text-xs font-medium"
        >
          Mega
        </Badge>
        <Badge
          v-if="species.traits.isGmax"
          variant="outline"
          class="text-xs font-medium"
        >
          G-Max
        </Badge>
      </div>
    </AppInfoCardRow>
    <AppInfoCardRow
      v-else
      title="Traits"
      text="None"
    />
  </AppInfoCard>
</template>

<script setup lang="ts">
import AppInfoCard from '@/components/AppInfoCard.vue';
import AppInfoCardRow from '@/components/AppInfoCardRow.vue';
import AppSpeciesPortrait from '@/components/AppSpeciesPortrait.vue';
import ElementalTypeBadges from '@/components/ElementalTypeBadges.vue';
import { Badge } from '@/components/ui/badge';
import {
  Tooltip,
  TooltipTrigger,
  TooltipContent,
} from '@/components/ui/tooltip';
import type { DexPokedexShowPageProps } from '@/pages/Dex/Pokedex/Show.vue';

defineProps<{
  species: DexPokedexShowPageProps['species'];
}>();
</script>
