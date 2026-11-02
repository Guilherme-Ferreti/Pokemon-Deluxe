<template>
  <AppInfoCard title="Pokédex Data">
    <AppInfoCardRow title="Regular/Shiny">
      <div class="flex gap-2">
        <Tooltip>
          <TooltipTrigger>
            <SpeciesPortrait
              v-if="page.props.overview.portraits.regular"
              :url="page.props.overview.portraits.regular"
              :name="page.props.species.name"
            />
          </TooltipTrigger>
          <TooltipContent>Regular</TooltipContent>
        </Tooltip>
        <Tooltip>
          <TooltipTrigger>
            <SpeciesPortrait
              v-if="page.props.overview.portraits.shiny"
              :url="page.props.overview.portraits.shiny"
              :name="page.props.species.name"
            />
          </TooltipTrigger>
          <TooltipContent>Shiny</TooltipContent>
        </Tooltip>
      </div>
    </AppInfoCardRow>
    <AppInfoCardRow
      title="Type(s)"
      data-test="pokedex-species-overview__types"
    >
      <ElementalTypeBadges :types="page.props.overview.types" />
    </AppInfoCardRow>
    <AppInfoCardRow
      title="Generation"
      data-test="pokedex-species-overview__generation"
      :text="$page.props.labels.generation[page.props.overview.generation]"
    />
    <AppInfoCardRow
      v-if="Object.values(page.props.overview.traits).some(Boolean)"
      title="Traits"
      data-test="pokedex-species-overview__traits"
    >
      <div class="flex flex-wrap gap-2">
        <Badge
          v-if="page.props.overview.traits.isBaby"
          variant="outline"
          class="text-xs font-medium"
        >
          Baby
        </Badge>
        <Badge
          v-if="page.props.overview.traits.isLegendary"
          variant="outline"
          class="text-xs font-medium"
        >
          Legendary
        </Badge>
        <Badge
          v-if="page.props.overview.traits.isMythical"
          variant="outline"
          class="text-xs font-medium"
        >
          Mythical
        </Badge>
        <Badge
          v-if="page.props.overview.traits.isMega"
          variant="outline"
          class="text-xs font-medium"
        >
          Mega
        </Badge>
        <Badge
          v-if="page.props.overview.traits.isGmax"
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
      data-test="pokedex-species-overview__traits"
    />
    <AppInfoCardRow
      v-if="page.props.overview.isGenderless"
      title="Gender Rate"
      text="Genderless"
      data-test="pokedex-species-overview__gender-rate"
    />
    <AppInfoCardRow
      v-else
      title="Gender Rate"
      data-test="pokedex-species-overview__gender-rate"
    >
      <span class="flex items-center gap-1 text-sm text-foreground">
        <span
          v-if="canBeMale"
          class="flex items-center gap-0.5"
        >
          {{ page.props.overview.malePercentage }}%

          <PokemonGenderIcon :gender="PokemonGender.MALE" />
        </span>
        <span v-if="canBeMale && canBeFemale">/</span>
        <span
          v-if="canBeFemale"
          class="flex items-center gap-0.5"
        >
          {{ page.props.overview.femalePercentage }}%
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

const canBeMale = page.props.overview.malePercentage !== 0;
const canBeFemale = page.props.overview.femalePercentage !== 0;
</script>
