<template>
  <AppInfoCard title="Pokédex Data">
    <AppInfoCardRow title="Regular/Shiny">
      <div class="flex gap-2">
        <Tooltip>
          <TooltipTrigger>
            <AppSpeciesPortrait
              v-if="metadata.portraits.regular"
              :url="metadata.portraits.regular"
              :name="speciesName"
            />
          </TooltipTrigger>
          <TooltipContent>Regular</TooltipContent>
        </Tooltip>
        <Tooltip>
          <TooltipTrigger>
            <AppSpeciesPortrait
              v-if="metadata.portraits.shiny"
              :url="metadata.portraits.shiny"
              :name="speciesName"
            />
          </TooltipTrigger>
          <TooltipContent>Shiny</TooltipContent>
        </Tooltip>
      </div>
    </AppInfoCardRow>
    <AppInfoCardRow
      title="Type(s)"
      data-test="pokedex-species-metadata__types"
    >
      <ElementalTypeBadges :types="metadata.types" />
    </AppInfoCardRow>
    <AppInfoCardRow
      title="Generation"
      data-test="pokedex-species-metadata__generation"
      :text="$page.props.labels.generation[metadata.generation]"
    />
    <AppInfoCardRow
      v-if="Object.values(metadata.traits).some(Boolean)"
      title="Traits"
      data-test="pokedex-species-metadata__traits"
    >
      <div class="flex flex-wrap gap-2">
        <Badge
          v-if="metadata.traits.isBaby"
          variant="outline"
          class="text-xs font-medium"
        >
          Baby
        </Badge>
        <Badge
          v-if="metadata.traits.isLegendary"
          variant="outline"
          class="text-xs font-medium"
        >
          Legendary
        </Badge>
        <Badge
          v-if="metadata.traits.isMythical"
          variant="outline"
          class="text-xs font-medium"
        >
          Mythical
        </Badge>
        <Badge
          v-if="metadata.traits.isMega"
          variant="outline"
          class="text-xs font-medium"
        >
          Mega
        </Badge>
        <Badge
          v-if="metadata.traits.isGmax"
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
      data-test="pokedex-species-metadata__traits"
    />
    <AppInfoCardRow
      v-if="metadata.isGenderless"
      title="Gender Rate"
      text="Genderless"
      data-test="pokedex-species-metadata__gender-rate"
    />
    <AppInfoCardRow
      v-else
      title="Gender Rate"
      data-test="pokedex-species-metadata__gender-rate"
    >
      <span class="flex items-center gap-1 text-sm text-foreground">
        <span
          v-if="canBeMale"
          class="flex items-center gap-0.5"
        >
          {{ metadata.malePercentage }}%

          <AppPokemonGenderIcon :gender="PokemonGender.MALE" />
        </span>
        <span v-if="canBeMale && canBeFemale">/</span>
        <span
          v-if="canBeFemale"
          class="flex items-center gap-0.5"
        >
          {{ metadata.femalePercentage }}%
          <AppPokemonGenderIcon :gender="PokemonGender.FEMALE" />
        </span>
      </span>
    </AppInfoCardRow>
  </AppInfoCard>
</template>

<script setup lang="ts">
import type { PokedexShowPageProps } from '@/app/Dex/types/page-props/PokedexShowPageProps';
import AppInfoCard from '@/shared/components/AppInfoCard.vue';
import AppInfoCardRow from '@/shared/components/AppInfoCardRow.vue';
import AppPokemonGenderIcon from '@/shared/components/AppPokemonGenderIcon.vue';
import AppSpeciesPortrait from '@/shared/components/AppSpeciesPortrait.vue';
import ElementalTypeBadges from '@/shared/components/ElementalTypeBadges.vue';
import { Badge } from '@/shared/components/ui/badge';
import {
  Tooltip,
  TooltipTrigger,
  TooltipContent,
} from '@/shared/components/ui/tooltip';
import { PokemonGender } from '@/shared/types/enums';
import type { Species } from '@/shared/types/response';

const props = defineProps<{
  speciesName: Species['name'];
  metadata: PokedexShowPageProps['metadata'];
}>();

const canBeMale = props.metadata.malePercentage !== 0;
const canBeFemale = props.metadata.femalePercentage !== 0;
</script>
