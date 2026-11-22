<template>
  <a
    href="#"
    class="group rounded-xl outline-none focus-visible:ring-3 focus-visible:ring-ring/50"
  >
    <Card
      class="h-full gap-0 overflow-hidden py-0 transition-colors group-hover:border-ring motion-reduce:transition-none"
    >
      <div
        class="flex h-24 items-end bg-linear-120 px-4 py-3 dark:from-muted dark:to-muted"
        :class="[gradientFrom[area.types[0]], gradientTo[area.types[1]]]"
      >
        <h2 class="text-lg font-bold text-foreground">{{ area.label }}</h2>
      </div>
      <div class="flex flex-col gap-3 px-4 pt-3 pb-4">
        <PokemonTypeBadges :types="area.types" />
        <span :class="cn(buttonVariants({ variant: 'outline' }), 'w-full')">
          Explore
        </span>
      </div>
    </Card>
  </a>
</template>

<script setup lang="ts">
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import { buttonVariants } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import { cn } from '@/shared/lib/utils';
import { PokemonType } from '@/shared/types/enums';
import type { ExploreArea } from '../../../types/page-props/ExploreIndexPageProps';

defineProps<{
  area: ExploreArea;
}>();

const gradientFrom: Record<PokemonType, string> = {
  [PokemonType.FIRE]: 'from-orange-100',
  [PokemonType.WATER]: 'from-blue-100',
  [PokemonType.GRASS]: 'from-green-100',
  [PokemonType.ELECTRIC]: 'from-yellow-100',
  [PokemonType.PSYCHIC]: 'from-pink-100',
  [PokemonType.ICE]: 'from-cyan-100',
  [PokemonType.FIGHTING]: 'from-red-100',
  [PokemonType.POISON]: 'from-purple-100',
  [PokemonType.GROUND]: 'from-amber-100',
  [PokemonType.FLYING]: 'from-sky-100',
  [PokemonType.BUG]: 'from-lime-100',
  [PokemonType.ROCK]: 'from-stone-100',
  [PokemonType.GHOST]: 'from-indigo-100',
  [PokemonType.DRAGON]: 'from-violet-100',
  [PokemonType.DARK]: 'from-gray-200',
  [PokemonType.STEEL]: 'from-slate-100',
  [PokemonType.NORMAL]: 'from-gray-100',
  [PokemonType.FAIRY]: 'from-rose-100',
};

const gradientTo: Record<PokemonType, string> = {
  [PokemonType.FIRE]: 'to-orange-100',
  [PokemonType.WATER]: 'to-blue-100',
  [PokemonType.GRASS]: 'to-green-100',
  [PokemonType.ELECTRIC]: 'to-yellow-100',
  [PokemonType.PSYCHIC]: 'to-pink-100',
  [PokemonType.ICE]: 'to-cyan-100',
  [PokemonType.FIGHTING]: 'to-red-100',
  [PokemonType.POISON]: 'to-purple-100',
  [PokemonType.GROUND]: 'to-amber-100',
  [PokemonType.FLYING]: 'to-sky-100',
  [PokemonType.BUG]: 'to-lime-100',
  [PokemonType.ROCK]: 'to-stone-100',
  [PokemonType.GHOST]: 'to-indigo-100',
  [PokemonType.DRAGON]: 'to-violet-100',
  [PokemonType.DARK]: 'to-gray-200',
  [PokemonType.STEEL]: 'to-slate-100',
  [PokemonType.NORMAL]: 'to-gray-100',
  [PokemonType.FAIRY]: 'to-rose-100',
};
</script>
