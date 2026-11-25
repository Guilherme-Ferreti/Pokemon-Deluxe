<template>
  <header
    class="flex flex-wrap items-center justify-between gap-3"
    data-test="battle-header"
  >
    <Head :title="titles[page.props.battle.type]" />
    <div class="flex items-center gap-3">
      <slot name="sprite" />
      <div class="flex flex-col gap-0.5">
        <h1
          class="text-2xl font-bold text-foreground"
          data-test="battle-header__title"
        >
          {{ titles[page.props.battle.type] }}
        </h1>
        <p class="text-sm text-muted-foreground">
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
