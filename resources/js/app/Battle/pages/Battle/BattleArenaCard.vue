<template>
  <Card
    class="battle-arena-card"
    data-test="battle-arena-card"
  >
    <CardContent class="battle-arena-card__content">
      <div class="battle-arena-card__side battle-arena-card__side--opponent">
        <CombatantPanel side="opponent" />
        <SpeciesSprite
          class="battle-arena-card__sprite"
          :url="page.props.opponent.sprite"
          :name="page.props.opponent.name"
        />
      </div>
      <div class="battle-arena-card__side battle-arena-card__side--player">
        <SpeciesSprite
          class="battle-arena-card__sprite"
          :url="page.props.player.sprite"
          :name="page.props.player.name"
        />
        <CombatantPanel side="player" />
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import SpeciesSprite from '@/shared/components/game/SpeciesSprite.vue';
import { Card, CardContent } from '@/shared/components/ui/card';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import CombatantPanel from './CombatantPanel.vue';

const page = usePage<BattleShowPageProps>();
</script>

<style scoped>
@reference '@css/app.css';

.battle-arena-card__content {
  @apply flex flex-col gap-2;
}

.battle-arena-card__side {
  @apply flex flex-wrap items-center gap-x-6 gap-y-4;
}

.battle-arena-card__side--opponent {
  @apply justify-end;
}

.battle-arena-card__side--player {
  @apply justify-start;
}

.battle-arena-card__sprite {
  @apply size-20 sm:size-24;
}
</style>
