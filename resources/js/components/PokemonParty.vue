<template>
  <Card class="party-card">
    <CardHeader>
      <CardTitle>Party</CardTitle>
    </CardHeader>
    <CardContent class="party-card__slots-wrapper">
      <div
        v-for="pokemon in pokemonList"
        :key="pokemon.id"
        class="party-card__pokemon-slot"
      >
        <img
          loading="lazy"
          :src="pokemon.frontSprite"
          :alt="pokemon.name"
        />
        <p class="party-card__pokemon-name">
          {{ pokemon.name }}
        </p>
        <p class="party-card__pokemon-level">Lv. {{ pokemon.level }}</p>
      </div>
      <div
        v-for="i in 6 - pokemonList.length"
        :key="i"
        class="party-card__empty-slot"
      >
        <span class="party-card__empty-slot-icon"><IconPlus /></span>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { IconPlus } from '@tabler/icons-vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { PokemonIndexPageProps } from '@/pages/Pokemon/Index.vue';

defineProps<{
  pokemonList: PokemonIndexPageProps['pokemonInParty'];
}>();
</script>

<style scoped>
@reference '../../css/app.css';

.party-card {
  @apply @container/card gap-3;
}

.party-card__slots-wrapper {
  @apply flex flex-wrap justify-center gap-3 @4xl/card:justify-between;
}

.party-card__pokemon-slot,
.party-card__empty-slot {
  @apply flex aspect-square w-full max-w-40 flex-col items-center justify-center rounded-lg border border-border p-2 transition-all hover:border-primary/50 hover:shadow-sm;
}

.party-card__pokemon-slot {
  @apply bg-card;
}

.party-card__pokemon-name {
  @apply text-center text-sm font-medium;
}

.party-card__pokemon-level {
  @apply text-center text-xs text-muted-foreground;
}

.party-card__empty-slot {
  @apply border-dashed bg-muted/30;
}

.party-card__empty-slot-icon {
  @apply text-muted-foreground/40;
}
</style>
