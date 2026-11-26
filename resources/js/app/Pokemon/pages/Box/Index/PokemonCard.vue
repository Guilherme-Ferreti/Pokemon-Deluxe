<template>
  <Card
    class="h-full gap-2 p-4 transition-shadow hover:shadow-md"
    :data-test="`pokemon-${pokemon.id}-card`"
  >
    <div class="grid grid-cols-[auto_1fr] gap-2">
      <SpeciesSprite
        :url="pokemon.frontSprite"
        :name="pokemon.name"
      />
      <div class="flex flex-col">
        <div class="flex justify-between gap-1">
          <p
            class="self-center text-sm font-semibold text-foreground"
            :data-test="`pokemon-${pokemon.id}-card__name`"
          >
            {{ pokemon.name }}
          </p>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button
                type="button"
                variant="ghost"
                size="icon-sm"
                class="text-ring"
                data-test="pokemon-card__open-dropdown-menu-button"
              >
                <IconDotsVertical />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem as-child>
                <AppNavLink
                  title="View"
                  data-test="pokemon-card__view-pokemon-link"
                  :url="route('pokemon.web.box.show', { pokemon: pokemon.id })"
                  :icon="IconEye"
                />
              </DropdownMenuItem>
              <DropdownMenuItem as-child>
                <AppNavLink
                  title="Pokédex"
                  data-test="pokemon-card__view-species-link"
                  :url="
                    route('dex.web.pokedex.show', {
                      species: pokemon.species.slug,
                    })
                  "
                  :icon="IconBook"
                />
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
        <PokemonTypeBadges
          class="mt-1.5"
          :data-test="`pokemon-${pokemon.id}-card__types`"
          :types="pokemon.species.types"
        />
      </div>
    </div>
    <div
      class="mt-auto flex items-center justify-between rounded bg-blue-100/40 px-2 py-1 text-xs text-muted-foreground dark:bg-transparent"
    >
      <div>
        <span
          class="flex items-center gap-0.5"
          :data-test="`pokemon-${pokemon.id}-card__gender`"
        >
          Gender: <PokemonGenderIcon :gender="pokemon.gender" />
        </span>
      </div>
      <div>
        <span aria-hidden="true">Lv.</span>
        <span class="sr-only">Level</span>
        <span
          class="font-medium text-foreground"
          :data-test="`pokemon-${pokemon.id}-card__level`"
        >
          {{ pokemon.level }}
        </span>
      </div>
      <span :data-test="`pokemon-${pokemon.id}-card__experience`">
        {{ pokemon.experience }} XP
      </span>
    </div>
    <div class="flex flex-col gap-0.5">
      <p
        class="text-xxs font-medium tracking-wide text-muted-foreground uppercase"
      >
        Moves
      </p>
      <ul class="grid grid-cols-2 gap-0.5">
        <li
          v-for="(move, index) in pokemon.moves"
          :key="move.name"
          class="flex items-center gap-1"
        >
          <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-blue-300"></span>
          <span
            class="truncate text-xs text-foreground"
            :title="move.name"
            :data-test="`pokemon-${pokemon.id}-card__move-${index + 1}`"
          >
            {{ move.name }}
          </span>
        </li>
      </ul>
    </div>
  </Card>
</template>

<script setup lang="ts">
import { IconBook, IconDotsVertical, IconEye } from '@tabler/icons-vue';
import PokemonGenderIcon from '@/shared/components/game/PokemonGenderIcon.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import SpeciesSprite from '@/shared/components/game/SpeciesSprite.vue';
import AppNavLink from '@/shared/components/layout/AppNavLink.vue';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/shared/components/ui/dropdown-menu';
import { route } from '@/shared/types/helpers/route';
import type { BoxIndexPageProps } from '../../../types/page-props/BoxIndexPageProps';

defineProps<{
  pokemon: BoxIndexPageProps['pokemonList']['data'][number];
}>();
</script>
