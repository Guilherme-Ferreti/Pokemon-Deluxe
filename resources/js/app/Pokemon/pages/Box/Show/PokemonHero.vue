<template>
  <Card
    class="@container/hero gap-0 p-0"
    data-test="pokemon-hero"
  >
    <div class="flex flex-col @xl/hero:flex-row">
      <div
        class="flex items-center justify-center bg-muted/40 p-6 @xl/hero:w-64"
      >
        <SpeciesSprite
          class="size-36"
          :url="pokemon.frontSprite"
          :name="pokemon.name"
        />
      </div>
      <div class="flex flex-1 flex-col gap-4 p-6">
        <div class="flex flex-col gap-2">
          <h1
            class="text-3xl font-bold text-foreground"
            data-test="pokemon-hero__name"
          >
            <PokemonDisplayName
              :name="pokemon.name"
              :gender="pokemon.gender"
            />
          </h1>
          <div class="flex flex-wrap items-center gap-1">
            <PokemonTypeBadges :types="pokemon.species.types" />
            <Badge
              variant="secondary"
              class="tabular-nums"
              data-test="pokemon-hero__level-indicator"
            >
              Lv {{ pokemon.level }}
            </Badge>
            <Badge
              variant="secondary"
              class="tabular-nums"
              data-test="pokemon-hero__boost-indicator"
            >
              Boost +{{ pokemon.boost }}
            </Badge>
            <Badge
              v-if="pokemon.isShiny"
              variant="outline"
              class="border-amber-300 bg-amber-100 text-amber-800 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-300"
            >
              <IconStarFilled />
              Shiny
            </Badge>
            <Tooltip v-if="pokemon.isLocked">
              <TooltipTrigger as-child>
                <Badge
                  variant="outline"
                  tabindex="0"
                  data-test="pokemon-hero__locked-indicator"
                >
                  <IconLock />
                  Locked
                </Badge>
              </TooltipTrigger>
              <TooltipContent>
                Can't be released or traded until it's unlocked.
              </TooltipContent>
            </Tooltip>
          </div>
        </div>
        <div class="flex max-w-md flex-col gap-1">
          <template v-if="pokemon.experience">
            <Progress
              class="h-1.5"
              aria-label="XP to the next level"
              :model-value="experiencePercentage"
            />
            <span
              class="text-xs text-muted-foreground tabular-nums"
              data-test="pokemon-hero__experience-indicator"
            >
              {{ pokemon.experience.current.toLocaleString('en-US') }} /
              {{ pokemon.experience.needed.toLocaleString('en-US') }} XP to Lv
              {{ pokemon.level + 1 }}
            </span>
          </template>
          <span
            v-else
            class="text-xs text-muted-foreground"
            data-test="pokemon-hero__experience-indicator"
          >
            Max level
          </span>
        </div>
        <div class="flex flex-col gap-1 text-sm text-muted-foreground">
          <span class="inline-flex flex-wrap items-center gap-1">
            Caught in a
            <span class="inline-flex items-center gap-0.5 text-foreground">
              <ItemSprite
                class="size-6"
                :name="pokemon.pokeball.name"
                :url="pokemon.pokeball.sprite"
              />
              {{ pokemon.pokeball.name }}
            </span>
            · {{ caughtOn }}
          </span>
        </div>
        <PokemonActions />
      </div>
    </div>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconLock, IconStarFilled } from '@tabler/icons-vue';
import { computed } from 'vue';
import ItemSprite from '@/shared/components/game/ItemSprite.vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import SpeciesSprite from '@/shared/components/game/SpeciesSprite.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Card } from '@/shared/components/ui/card';
import { Progress } from '@/shared/components/ui/progress';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';
import PokemonActions from './PokemonActions.vue';

const page = usePage<BoxShowPageProps>();

const pokemon = computed(() => page.props.pokemon);

const experiencePercentage = computed(() =>
  pokemon.value.experience
    ? (pokemon.value.experience.current / pokemon.value.experience.needed) * 100
    : 100,
);

const caughtOn = computed(() =>
  new Date(pokemon.value.caughtAt).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    timeZone: 'UTC',
  }),
);
</script>
