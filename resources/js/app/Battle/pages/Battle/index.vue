<template>
  <BattleHeader>
    <RunBattleDialog v-if="page.props.battle.status === BattleStatus.ONGOING" />
  </BattleHeader>
  <div
    ref="grid"
    class="battle-page__grid"
  >
    <div class="battle-page__column battle-page__column--left">
      <BattleTeamRail class="battle-page__team" />
    </div>
    <div class="battle-page__column battle-page__column--middle">
      <Transition
        leave-active-class="battle-page__battlefield--leaving"
        leave-to-class="battle-page__battlefield--gone"
        @after-leave="isResultShown = true"
      >
        <BattlefieldCard
          v-if="isBattlefieldShown"
          class="battle-page__battlefield"
        />
      </Transition>
      <PokemonFaintedCard
        v-if="page.props.battle.isAwaitingSendOut"
        class="battle-page__moves"
      />
      <MovesCard
        v-else-if="page.props.result === null"
        class="battle-page__moves"
      />
      <BattleResultCard
        v-if="page.props.result !== null && isResultShown"
        class="battle-page__result"
      />
    </div>
    <div class="battle-page__column battle-page__column--right">
      <BattleLogCard class="battle-page__log" />
    </div>
  </div>
  <SendOutSheet :open="page.props.battle.isAwaitingSendOut && isPhoneLayout" />
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useElementSize } from '@vueuse/core';
import { computed, onBeforeUnmount, ref, useTemplateRef, watch } from 'vue';
import { BattleStatus } from '@/shared/types/enums';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BattlefieldCard from './BattlefieldCard.vue';
import BattleHeader from './BattleHeader.vue';
import BattleLogCard from './BattleLogCard.vue';
import BattleResultCard from './BattleResultCard.vue';
import BattleTeamRail from './BattleTeamRail.vue';
import MovesCard from './MovesCard.vue';
import PokemonFaintedCard from './PokemonFaintedCard.vue';
import RunBattleDialog from './RunBattleDialog.vue';
import SendOutSheet from './SendOutSheet.vue';

const page = usePage<BattleShowPageProps>();

const HP_DRAIN_MILLISECONDS = 700;

const WIDE_LAYOUT_MIN_WIDTH_IN_REM = 64;

const { width: gridWidth } = useElementSize(useTemplateRef('grid'));

const isPhoneLayout = computed(
  () =>
    gridWidth.value > 0 &&
    gridWidth.value <
      WIDE_LAYOUT_MIN_WIDTH_IN_REM *
        parseFloat(getComputedStyle(document.documentElement).fontSize),
);

const isOngoing = page.props.battle.status === BattleStatus.ONGOING;
const isBattlefieldShown = ref(isOngoing);
const isResultShown = ref(!isOngoing);

let hideBattlefieldTimeout: ReturnType<typeof setTimeout> | undefined;

watch(
  () => page.props.battle.status,
  (status) => {
    if (status !== BattleStatus.ONGOING && isBattlefieldShown.value) {
      hideBattlefieldTimeout = setTimeout(() => {
        isBattlefieldShown.value = false;
      }, HP_DRAIN_MILLISECONDS);
    }
  },
);

onBeforeUnmount(() => clearTimeout(hideBattlefieldTimeout));
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

.battle-page__result {
  @apply order-1 @5xl/main-content:order-0;
}

.battle-page__battlefield {
  @apply order-2 @5xl/main-content:order-0;
}

.battle-page__battlefield--leaving {
  @apply transition-opacity duration-450 ease-out motion-reduce:transition-none;
}

.battle-page__battlefield--gone {
  @apply opacity-0;
}

.battle-page__moves {
  @apply order-3 @5xl/main-content:order-0;
}

.battle-page__team {
  @apply order-4 @5xl/main-content:order-0;
}

.battle-page__log {
  @apply order-5 @5xl/main-content:order-0;
}
</style>
