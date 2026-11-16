<template>
  <Card
    class="moves-card"
    data-test="moves-card"
  >
    <CardHeader class="moves-card__header">
      <CardTitle>Moves</CardTitle>
      <span class="moves-card__turn">Turn {{ page.props.battle.turn }}</span>
    </CardHeader>
    <CardContent class="moves-card__grid">
      <Button
        v-for="(move, index) in page.props.moves"
        :key="move.id"
        type="button"
        variant="outline"
        class="moves-card__move-button"
        :data-test="`moves-card__move-${index + 1}-button`"
        :disabled="form.processing"
        @click="takeTurn(index + 1)"
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
          <span
            v-else-if="move.isSuperEffective"
            class="moves-card__move-super-effective"
          >
            <IconArrowUp
              class="size-4"
              aria-hidden="true"
            />
            Super effective
          </span>
        </span>
        <span class="moves-card__move-meta">
          <PokemonTypeBadges :types="[move.type]" />
          <span>Power {{ move.power ?? '—' }}</span>
        </span>
      </Button>
    </CardContent>
    <CardFooter>
      <SwitchPokemonDialog :disabled="form.processing" />
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconArrowUp } from '@tabler/icons-vue';
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import SwitchPokemonDialog from './SwitchPokemonDialog.vue';

const page = usePage<BattleShowPageProps>();

const form = useForm({
  moveSlot: 0,
});

function takeTurn(moveSlot: number): void {
  form.moveSlot = moveSlot;

  form.post(route('battle.web.turn'), {
    preserveScroll: true,
  });
}
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

.moves-card__move-super-effective {
  @apply flex items-center gap-0.5 text-xs font-semibold whitespace-nowrap text-green-700 dark:text-green-400;
}

.moves-card__move-meta {
  @apply flex flex-wrap items-center justify-between gap-1 text-xs text-muted-foreground;
}
</style>
