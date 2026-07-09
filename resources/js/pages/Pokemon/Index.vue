<template>
  <Card class="@container/card">
    <CardHeader>
      <CardTitle>Party</CardTitle>
    </CardHeader>
    <CardContent
      class="grid gap-3 @sm/card:grid-cols-2 @md/card:grid-cols-3 @4xl/card:grid-cols-6"
    >
      <div
        v-for="pokemon in pokemonInParty"
        :key="pokemon.id"
        class="flex flex-col items-center justify-center rounded-lg border border-border bg-card p-2 transition-all hover:border-primary/50 hover:shadow-sm @4xl/card:aspect-square"
      >
        <img
          :src="pokemon.frontSprite"
          :alt="pokemon.name"
          class="size-24"
        />
        <p class="w-full truncate text-center text-sm font-medium">
          {{ pokemon.name }}
        </p>
        <p class="text-center text-xs text-muted-foreground">
          Lv. {{ pokemon.level }}
        </p>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
  pokemonInParty: {
    id: string;
    name: string;
    frontSprite?: string;
    level: number;
  }[];
}>();
</script>
