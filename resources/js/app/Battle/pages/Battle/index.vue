<template>
  <BattleHeader>
    <RunBattleDialog v-if="page.props.battle.status === BattleStatus.ONGOING" />
  </BattleHeader>
  <div class="battle-page__grid">
    <div class="battle-page__column battle-page__column--middle">
      <BattleArenaCard class="battle-page__battlefield" />
      <MovesCard
        v-if="page.props.result === null"
        class="battle-page__moves"
      />
      <BattleResultCard
        v-else
        class="battle-page__result"
      />
    </div>
    <div class="battle-page__column battle-page__column--right">
      <BattleLogCard class="battle-page__log" />
    </div>
  </div>
  <SendOutDialog />
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { BattleStatus } from '@/shared/types/enums';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BattleArenaCard from './BattleArenaCard.vue';
import BattleHeader from './BattleHeader.vue';
import BattleLogCard from './BattleLogCard.vue';
import BattleResultCard from './BattleResultCard.vue';
import MovesCard from './MovesCard.vue';
import RunBattleDialog from './RunBattleDialog.vue';
import SendOutDialog from './SendOutDialog.vue';

const page = usePage<BattleShowPageProps>();
</script>

<style scoped>
@reference '@css/app.css';

.battle-page__grid {
  @apply grid grid-cols-1 gap-4 @5xl/main-content:grid-cols-[18rem_minmax(0,1fr)_18rem] @5xl/main-content:items-start;
}

.battle-page__column {
  @apply contents @5xl/main-content:flex @5xl/main-content:min-w-0 @5xl/main-content:flex-col @5xl/main-content:gap-4;
}

.battle-page__column--middle {
  @apply @5xl/main-content:col-start-2;
}

.battle-page__column--right {
  @apply @5xl/main-content:col-start-3;
}

.battle-page__result {
  @apply order-1 @5xl/main-content:order-none;
}

.battle-page__battlefield {
  @apply order-2 @5xl/main-content:order-none;
}

.battle-page__moves {
  @apply order-3 @5xl/main-content:order-none;
}

.battle-page__log {
  @apply order-5 @5xl/main-content:order-none;
}
</style>
