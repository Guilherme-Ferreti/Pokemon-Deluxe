<template>
  <Card
    class="gap-2 border bg-card p-3 text-card-foreground shadow transition-shadow hover:shadow-md"
  >
    <div class="flex gap-2">
      <img
        class="size-20 shrink-0"
        loading="lazy"
        :src="pokemon.frontSprite"
        :alt="pokemon.name"
      />
      <div class="flex flex-col gap-1">
        <p class="text-sm font-semibold text-foreground">
          {{ pokemon.name }}
        </p>
        <ElementalTypeBadges :types="pokemon.pokedex.types" />
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
          <span class="truncate text-xs text-foreground">{{ move.name }}</span>
        </li>
      </ul>
    </div>
  </Card>
</template>

<script setup lang="ts">
import type { PokemonIndexPageProps } from '@/pages/Pokemon/Index.vue';
import ElementalTypeBadges from './ElementalTypeBadges.vue';
import { Card } from './ui/card';

defineProps<{
  pokemon: PokemonIndexPageProps['pokemonBox']['data'][number];
}>();
</script>
