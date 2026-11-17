<template>
  <Card
    class="battlefield-card"
    data-test="battlefield-card"
    aria-label="Battlefield"
  >
    <CardContent class="battlefield-card__content">
      <div class="battlefield-card__side battlefield-card__side--opponent">
        <BattlefieldCombatantCard side="opponent" />
        <SpeciesSprite
          :class="[
            'battlefield-card__sprite',
            {
              'battlefield-card__sprite--fainted':
                page.props.opponent.currentHp === 0,
            },
          ]"
          :url="page.props.opponent.sprite"
          :name="page.props.opponent.name"
        />
      </div>
      <div class="battlefield-card__side battlefield-card__side--player">
        <SpeciesSprite
          :class="[
            'battlefield-card__sprite battlefield-card__sprite--player',
            {
              'battlefield-card__sprite--fainted':
                page.props.player.currentHp === 0,
            },
          ]"
          :url="page.props.player.sprite"
          :name="page.props.player.name"
        />
        <BattlefieldCombatantCard side="player" />
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import SpeciesSprite from '@/shared/components/game/SpeciesSprite.vue';
import { Card, CardContent } from '@/shared/components/ui/card';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BattlefieldCombatantCard from './BattlefieldCombatantCard.vue';

const page = usePage<BattleShowPageProps>();
</script>

<style scoped>
@reference '@css/app.css';

.battlefield-card {
  @apply @container/battlefield overflow-hidden;
}

.battlefield-card__content {
  @apply flex flex-col gap-1 px-3 @sm/battlefield:px-6;
}

.battlefield-card__side {
  @apply flex items-end gap-2.5;
}

.battlefield-card__side--opponent {
  @apply items-start justify-end;
}

.battlefield-card__sprite {
  @apply size-20 transition-opacity duration-400 motion-reduce:transition-none @sm/battlefield:size-28;
}

.battlefield-card__sprite--player {
  @apply size-22 @sm/battlefield:size-32;
}

.battlefield-card__sprite--fainted {
  @apply opacity-35;
}
</style>
