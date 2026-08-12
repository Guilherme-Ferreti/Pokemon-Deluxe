<template>
  <li>
    <Card
      class="gap-2 border bg-card p-3 text-card-foreground shadow transition-shadow hover:shadow-md"
    >
      <div class="grid grid-cols-[auto_1fr] gap-2">
        <AppPokemonSprite
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
                    title="Pokedex"
                    url="#"
                    :icon="IconBook"
                  />
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
          <ElementalTypeBadges
            class="mt-1.5"
            :types="pokemon.pokedex.types"
          />
        </div>
      </div>
      <div
        class="flex justify-between rounded bg-blue-100/40 px-2 py-1 text-xs text-muted-foreground dark:bg-transparent"
      >
        <span>
          Lv.
          <span class="font-medium text-foreground">{{ pokemon.level }}</span>
        </span>
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
            <span class="truncate text-xs text-foreground">{{
              move.name
            }}</span>
          </li>
        </ul>
      </div>
    </Card>
  </li>
</template>

<script setup lang="ts">
import { IconBook, IconDotsVertical, IconEye } from '@tabler/icons-vue';
import type { PokemonBoxPageProps } from '@/pages/Pokemon/Box.vue';
import AppNavLink from './AppNavLink.vue';
import AppPokemonSprite from './AppPokemonSprite.vue';
import ElementalTypeBadges from './ElementalTypeBadges.vue';
import { Button } from './ui/button';
import { Card } from './ui/card';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
} from './ui/dropdown-menu';

defineProps<{
  pokemon: PokemonBoxPageProps['box']['data'][number];
}>();
</script>
