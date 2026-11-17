<template>
  <header
    class="battle-header"
    data-test="battle-header"
  >
    <Head :title="titles[page.props.battle.type]" />
    <div class="battle-header__heading">
      <slot name="sprite" />
      <div class="battle-header__text">
        <h1
          class="battle-header__title"
          data-test="battle-header__title"
        >
          {{ titles[page.props.battle.type] }}
        </h1>
        <p class="battle-header__description">
          {{ description }}
        </p>
      </div>
    </div>
    <slot />
  </header>
</template>

<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { BattleStatus, BattleType } from '@/shared/types/enums';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

const titles: Record<BattleType, string> = {
  [BattleType.WILD]: 'Wild battle',
  [BattleType.TRAINER]: 'Trainer battle',
  [BattleType.GYM]: 'Gym battle',
  [BattleType.ELITE_FOUR]: 'Elite Four battle',
  [BattleType.BATTLE_TOWER]: 'Battle Tower battle',
};

const shownTurn = computed(() =>
  page.props.battle.status === BattleStatus.ONGOING
    ? page.props.battle.turn
    : Math.max(1, page.props.battle.turn - 1),
);

const description = computed(() =>
  page.props.battle.type === BattleType.WILD
    ? `A wild ${page.props.opponent.name} appeared · Turn ${shownTurn.value}`
    : `Turn ${shownTurn.value}`,
);
</script>

<style scoped>
@reference '@css/app.css';

.battle-header {
  @apply flex flex-wrap items-center justify-between gap-3;
}

.battle-header__heading {
  @apply flex items-center gap-3;
}

.battle-header__text {
  @apply flex flex-col gap-0.5;
}

.battle-header__title {
  @apply text-2xl font-bold text-foreground;
}

.battle-header__description {
  @apply text-sm text-muted-foreground;
}
</style>
