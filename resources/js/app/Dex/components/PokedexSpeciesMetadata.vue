<template>
  <AppInfoCard title="Pokédex Data">
    <AppInfoCardRow title="Regular/Shiny">
      <div class="flex gap-2">
        <Tooltip>
          <TooltipTrigger>
            <SpeciesPortrait
              v-if="page.props.metadata.portraits.regular"
              :url="page.props.metadata.portraits.regular"
              :name="page.props.species.name"
            />
          </TooltipTrigger>
          <TooltipContent>Regular</TooltipContent>
        </Tooltip>
        <Tooltip>
          <TooltipTrigger>
            <SpeciesPortrait
              v-if="page.props.metadata.portraits.shiny"
              :url="page.props.metadata.portraits.shiny"
              :name="page.props.species.name"
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
      <ElementalTypeBadges :types="page.props.metadata.types" />
    </AppInfoCardRow>
    <AppInfoCardRow
      title="Generation"
      data-test="pokedex-species-metadata__generation"
      :text="$page.props.labels.generation[page.props.metadata.generation]"
    />
    <AppInfoCardRow
      v-if="Object.values(page.props.metadata.traits).some(Boolean)"
      title="Traits"
      data-test="pokedex-species-metadata__traits"
    >
      <div class="flex flex-wrap gap-2">
        <Badge
          v-if="page.props.metadata.traits.isBaby"
          variant="outline"
          class="text-xs font-medium"
        >
          Baby
        </Badge>
        <Badge
          v-if="page.props.metadata.traits.isLegendary"
          variant="outline"
          class="text-xs font-medium"
        >
          Legendary
        </Badge>
        <Badge
          v-if="page.props.metadata.traits.isMythical"
          variant="outline"
          class="text-xs font-medium"
        >
          Mythical
        </Badge>
        <Badge
          v-if="page.props.metadata.traits.isMega"
          variant="outline"
          class="text-xs font-medium"
        >
          Mega
        </Badge>
        <Badge
          v-if="page.props.metadata.traits.isGmax"
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
      v-if="page.props.metadata.isGenderless"
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
          {{ page.props.metadata.malePercentage }}%

          <PokemonGenderIcon :gender="PokemonGender.MALE" />
        </span>
        <span v-if="canBeMale && canBeFemale">/</span>
        <span
          v-if="canBeFemale"
          class="flex items-center gap-0.5"
        >
          {{ page.props.metadata.femalePercentage }}%
          <PokemonGenderIcon :gender="PokemonGender.FEMALE" />
        </span>
      </span>
    </AppInfoCardRow>
  </AppInfoCard>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { PokedexShowPageProps } from '@/app/Dex/types/page-props/PokedexShowPageProps';
import AppInfoCard from '@/shared/components/content/AppInfoCard.vue';
import AppInfoCardRow from '@/shared/components/content/AppInfoCardRow.vue';
import ElementalTypeBadges from '@/shared/components/game/ElementalTypeBadges.vue';
import PokemonGenderIcon from '@/shared/components/game/PokemonGenderIcon.vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { Badge } from '@/shared/components/ui/badge';
import {
  Tooltip,
  TooltipTrigger,
  TooltipContent,
} from '@/shared/components/ui/tooltip';
import { PokemonGender } from '@/shared/types/enums';

const page = usePage<PokedexShowPageProps>();

const canBeMale = page.props.metadata.malePercentage !== 0;
const canBeFemale = page.props.metadata.femalePercentage !== 0;
</script>
