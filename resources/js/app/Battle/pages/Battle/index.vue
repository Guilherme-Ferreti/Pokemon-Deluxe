<template>
  <BattleHeader />
  <div
    ref="grid"
    class="battle-page__grid"
  >
    <div class="battle-page__column battle-page__column--left">
      <BattleTeamRail class="battle-page__team" />
    </div>
    <div class="battle-page__column battle-page__column--middle">
      <BattlefieldCard class="battle-page__battlefield" />
      <PokemonFaintedCard
        v-if="page.props.battle.isAwaitingSendOut"
        class="battle-page__moves"
      />
      <MovesCard
        v-else-if="page.props.result === null"
        class="battle-page__moves"
      />
      <BattleOptionsCard
        v-if="page.props.result === null"
        class="battle-page__options"
      />
      <BattleResultCard
        v-if="page.props.result !== null"
        class="battle-page__result"
      />
    </div>
    <div class="battle-page__column battle-page__column--right">
      <OpponentTeamRail
        v-if="page.props.battle.type !== BattleType.WILD"
        class="battle-page__opponent-team"
      />
      <BattleLogCard class="battle-page__log" />
    </div>
  </div>
  <SendOutSheet :open="page.props.battle.isAwaitingSendOut && isPhoneLayout" />
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useElementSize } from '@vueuse/core';
import { computed, useTemplateRef } from 'vue';
import { BattleType } from '@/shared/types/enums';
import { provideBattleLayout } from '../../composables/useBattleLayout';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BattlefieldCard from './BattlefieldCard.vue';
import BattleHeader from './BattleHeader.vue';
import BattleLogCard from './BattleLogCard.vue';
import BattleOptionsCard from './BattleOptionsCard.vue';
import BattleResultCard from './BattleResultCard.vue';
import BattleTeamRail from './BattleTeamRail.vue';
import MovesCard from './MovesCard.vue';
import OpponentTeamRail from './OpponentTeamRail.vue';
import PokemonFaintedCard from './PokemonFaintedCard.vue';
import SendOutSheet from './SendOutSheet.vue';

const page = usePage<BattleShowPageProps>();

const WIDE_LAYOUT_MIN_WIDTH_IN_REM = 64;

const { width: gridWidth } = useElementSize(useTemplateRef('grid'));

const isPhoneLayout = computed(
  () =>
    gridWidth.value > 0 &&
    gridWidth.value <
      WIDE_LAYOUT_MIN_WIDTH_IN_REM *
        parseFloat(getComputedStyle(document.documentElement).fontSize),
);

provideBattleLayout(isPhoneLayout);
</script>

<style scoped>
@reference '@css/app.css';

.battle-page__grid {
  @apply grid grid-cols-1 gap-4 @5xl/main-content:grid-cols-[18rem_minmax(0,1fr)_18rem] @5xl/main-content:items-start;
}

.battle-page__column {
  @apply contents @5xl/main-content:flex @5xl/main-content:min-w-0 @5xl/main-content:flex-col @5xl/main-content:gap-4;
}

.battle-page__column--left {
  @apply @5xl/main-content:col-start-1;
}

.battle-page__column--middle {
  @apply @5xl/main-content:col-start-2;
}

.battle-page__column--right {
  @apply @5xl/main-content:col-start-3;
}

.battle-page__battlefield {
  @apply order-1 @5xl/main-content:order-0;
}

.battle-page__result {
  @apply order-2 @5xl/main-content:order-0;
}

.battle-page__moves {
  @apply order-3 @5xl/main-content:order-0;
}

.battle-page__team {
  @apply order-4 @5xl/main-content:order-0;
}

.battle-page__opponent-team {
  @apply order-5 @5xl/main-content:order-0;
}

.battle-page__options {
  @apply order-6 @5xl/main-content:order-0;
}

.battle-page__log {
  @apply order-7 @5xl/main-content:order-0;
}
</style>
