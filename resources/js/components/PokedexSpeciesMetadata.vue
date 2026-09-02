<template>
  <Card class="gap-4">
    <CardHeader>
      <CardTitle>Pokédex Data</CardTitle>
    </CardHeader>
    <CardContent class="flex flex-col gap-4">
      <div class="metadata__line">
        <span class="metadata__line-title">Regular/Shiny</span>
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
      </div>
      <div class="metadata__line">
        <span class="metadata__line-title">Type(s)</span>
        <ElementalTypeBadges :types="species.types" />
      </div>
      <div class="metadata__line">
        <span class="metadata__line-title">Generation</span>
        <span class="metadata__line-text">
          {{ species.generation }}
        </span>
      </div>
      <div class="metadata__line">
        <span class="metadata__line-title">Traits</span>
        <div
          v-if="Object.values(species.traits).some(Boolean)"
          class="flex flex-wrap gap-2"
        >
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
        <span
          v-else
          class="metadata__line-text"
        >
          None
        </span>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import AppSpeciesPortrait from '@/components/AppSpeciesPortrait.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Tooltip,
  TooltipTrigger,
  TooltipContent,
} from '@/components/ui/tooltip';
import type { DexPokedexShowPageProps } from '@/pages/Dex/Pokedex/Show.vue';
import ElementalTypeBadges from './ElementalTypeBadges.vue';

defineProps<{
  species: DexPokedexShowPageProps['species'];
}>();
</script>

<style lang="css" scoped>
@reference '../../css/app.css';

.metadata__line {
  @apply flex flex-wrap items-center justify-between gap-3;
}

.metadata__line-title {
  @apply text-sm font-medium text-muted-foreground;
}

.metadata__line-text {
  @apply text-sm text-foreground;
}
</style>
