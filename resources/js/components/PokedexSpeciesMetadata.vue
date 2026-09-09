<template>
  <AppInfoCard
    title="Pokédex Data"
    data-test="pokedex-species-metadata"
  >
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
    <AppInfoCardRow
      v-if="species.isGenderless"
      title="Gender Rate"
      text="Genderless"
    />
    <AppInfoCardRow
      v-else
      title="Gender Rate"
    >
      <span class="flex items-center gap-1 text-sm text-foreground">
        <span
          v-if="canBeMale"
          class="flex items-center gap-0.5"
        >
          {{ species.malePercentage }}%

          <AppPokemonGenderIcon :gender="PokemonGender.MALE" />
        </span>
        <span v-if="canBeMale && canBeFemale">/</span>
        <span
          v-if="canBeFemale"
          class="flex items-center gap-0.5"
        >
          {{ species.femalePercentage }}%
          <AppPokemonGenderIcon :gender="PokemonGender.FEMALE" />
        </span>
      </span>
    </AppInfoCardRow>
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
import { PokemonGender } from '@/types';
import AppPokemonGenderIcon from './AppPokemonGenderIcon.vue';

const props = defineProps<{
  species: DexPokedexShowPageProps['species'];
}>();

const canBeMale = props.species.malePercentage !== 0;
const canBeFemale = props.species.femalePercentage !== 0;
</script>
