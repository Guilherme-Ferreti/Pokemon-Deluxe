<template>
  <div class="flex flex-wrap gap-1">
    <Badge
      variant="secondary"
      class="dark:border-none dark:bg-muted dark:text-foreground"
      :class="colors[types[0]]"
    >
      {{ $page.props.labels.elementalType[types[0]] }}
    </Badge>
    <Badge
      v-if="types[1]"
      variant="secondary"
      class="dark:border-none dark:bg-muted dark:text-foreground"
      :class="colors[types[1]]"
    >
      {{ $page.props.labels.elementalType[types[1]] }}
    </Badge>
  </div>
</template>

<script setup lang="ts">
import { Badge } from '@/shared/components/ui/badge';
import { ElementalType } from '@/shared/types/enums';
import type { Species } from '@/shared/types/models';

defineProps<{
  types: Species['types'];
}>();

const colors = {
  [ElementalType.FIRE]: 'bg-orange-100 text-orange-700 border-orange-200',
  [ElementalType.WATER]: 'bg-blue-100 text-blue-700 border-blue-200',
  [ElementalType.GRASS]: 'bg-green-100 text-green-700 border-green-200',
  [ElementalType.ELECTRIC]: 'bg-yellow-100 text-yellow-700 border-yellow-200',
  [ElementalType.PSYCHIC]: 'bg-pink-100 text-pink-700 border-pink-200',
  [ElementalType.ICE]: 'bg-cyan-100 text-cyan-700 border-cyan-200',
  [ElementalType.FIGHTING]: 'bg-red-100 text-red-700 border-red-200',
  [ElementalType.POISON]: 'bg-purple-100 text-purple-700 border-purple-200',
  [ElementalType.GROUND]: 'bg-amber-100 text-amber-700 border-amber-200',
  [ElementalType.FLYING]: 'bg-sky-100 text-sky-700 border-sky-200',
  [ElementalType.BUG]: 'bg-lime-100 text-lime-700 border-lime-200',
  [ElementalType.ROCK]: 'bg-stone-100 text-stone-700 border-stone-200',
  [ElementalType.GHOST]: 'bg-indigo-100 text-indigo-700 border-indigo-200',
  [ElementalType.DRAGON]: 'bg-violet-100 text-violet-700 border-violet-200',
  [ElementalType.DARK]: 'bg-gray-200 text-gray-700 border-gray-300',
  [ElementalType.STEEL]: 'bg-slate-100 text-slate-600 border-slate-200',
  [ElementalType.NORMAL]: 'bg-gray-100 text-gray-600 border-gray-200',
  [ElementalType.FAIRY]: 'bg-rose-100 text-rose-700 border-rose-200',
};
</script>
