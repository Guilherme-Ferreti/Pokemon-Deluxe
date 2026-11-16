<template>
  <Card class="moves-card">
    <CardHeader class="moves-card__header">
      <CardTitle>Moves</CardTitle>
      <span class="moves-card__turn">Turn {{ page.props.battle.turn }}</span>
    </CardHeader>
    <CardContent class="moves-card__grid">
      <Button
        v-for="move in page.props.moves"
        :key="move.id"
        variant="outline"
        class="moves-card__move-button"
        disabled
      >
        <span class="moves-card__move-top">
          <MoveNameLabel
            class="moves-card__move-name"
            :name="move.name"
            :is-z-move="move.isZMove"
            :is-dynamax-move="move.isDynamaxMove"
          />
          <span
            v-if="move.power === null"
            class="moves-card__move-status"
          >
            Status
          </span>
        </span>
        <span class="moves-card__move-meta">
          <PokemonTypeBadges :types="[move.type]" />
          <span>Power {{ move.power ?? '—' }}</span>
        </span>
      </Button>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();
</script>

<style scoped>
@reference '@css/app.css';

.moves-card__header {
  @apply flex items-center justify-between;
}

.moves-card__turn {
  @apply text-sm text-muted-foreground tabular-nums;
}

.moves-card__grid {
  @apply grid grid-cols-2 gap-2;
}

.moves-card__move-button {
  @apply h-auto min-h-16 flex-col items-stretch gap-1.5 px-3 py-2 text-left whitespace-normal;
}

.moves-card__move-top {
  @apply flex items-center justify-between gap-2;
}

.moves-card__move-name {
  @apply font-semibold;
}

.moves-card__move-status {
  @apply text-xs text-muted-foreground;
}

.moves-card__move-meta {
  @apply flex flex-wrap items-center justify-between gap-1 text-xs text-muted-foreground;
}
</style>
