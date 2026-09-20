<template>
  <li>
    <Card class="gap-2 p-4 transition-shadow hover:shadow-md">
      <div class="grid grid-cols-[auto_1fr] gap-2">
        <AppSpeciesSprite
          :url="pokemon.frontSprite"
          :name="pokemon.name"
        />
        <div class="flex flex-col">
          <div class="flex justify-between gap-1">
            <p class="self-center text-sm font-semibold text-foreground">
              {{ pokemon.name }}
            </p>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon-sm"
                  class="text-ring"
                  data-test="pokemon-box-card__open-dropdown-menu-button"
                >
                  <IconDotsVertical />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem as-child>
                  <AppNavLink
                    title="View"
                    url="#"
                    :icon="IconEye"
                  />
                </DropdownMenuItem>
                <DropdownMenuItem as-child>
                  <AppNavLink
                    title="Pokédex"
                    data-test="pokemon-box-card__view-species-link"
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
          <ElementalTypeBadges
            class="mt-1.5"
            :types="pokemon.species.types"
          />
        </div>
      </div>
      <div
        class="flex items-center justify-between rounded bg-blue-100/40 px-2 py-1 text-xs text-muted-foreground dark:bg-transparent"
      >
        <div>
          <span class="flex items-center gap-0.5">
            Gender: <AppPokemonGenderIcon :gender="pokemon.gender" />
          </span>
        </div>
        <div>
          <span aria-hidden="true">Lv.</span>
          <span class="sr-only">Level</span>
          <span class="font-medium text-foreground">{{ pokemon.level }}</span>
        </div>
        <span>{{ pokemon.experience }} XP</span>
      </div>
      <div class="flex flex-col gap-0.5">
        <p
          class="text-xxs font-medium tracking-wide text-muted-foreground uppercase"
        >
          Moves
        </p>
        <ul class="grid grid-cols-2 gap-0.5">
          <li
            v-for="move in pokemon.moves"
            :key="move.name"
            class="flex items-center gap-1"
          >
            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-blue-300"></span>
            <span
              class="truncate text-xs text-foreground"
              :title="move.name"
            >
              {{ move.name }}
            </span>
          </li>
        </ul>
      </div>
    </Card>
  </li>
</template>

<script setup lang="ts">
import { IconBook, IconDotsVertical, IconEye } from '@tabler/icons-vue';
import type { PokemonBoxIndexPageProps } from '@/app/Pokemon/types/BoxIndexPageProps';
import AppNavLink from '@/shared/components/AppNavLink.vue';
import AppPokemonGenderIcon from '@/shared/components/AppPokemonGenderIcon.vue';
import AppSpeciesSprite from '@/shared/components/AppSpeciesSprite.vue';
import ElementalTypeBadges from '@/shared/components/ElementalTypeBadges.vue';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from '@/shared/components/ui/dropdown-menu';
import { route } from '@/shared/types/helpers/route';

defineProps<{
  pokemon: PokemonBoxIndexPageProps['pokemonList']['data'][number];
}>();
</script>
