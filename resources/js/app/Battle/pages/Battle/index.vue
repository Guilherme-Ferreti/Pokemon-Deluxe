<template>
  <Head :title="titles[page.props.battle.type]" />
  <AppPageHeader
    :title="titles[page.props.battle.type]"
    :description="`A wild ${page.props.opponent.name} appeared.`"
  >
    <RunBattleDialog v-if="page.props.battle.status === BattleStatus.ONGOING" />
  </AppPageHeader>
  <AppSection>
    <BattleArenaCard />
    <div class="battle-page__bottom">
      <MovesCard
        v-if="page.props.result === null"
        class="battle-page__moves-card"
      />
      <BattleResultCard
        v-else
        class="battle-page__moves-card"
      />
      <BattleLogCard class="battle-page__log-card" />
    </div>
  </AppSection>
  <SendOutDialog />
</template>

<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AppSection from '@/shared/components/content/AppSection.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import { BattleStatus, BattleType } from '@/shared/types/enums';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BattleArenaCard from './BattleArenaCard.vue';
import BattleLogCard from './BattleLogCard.vue';
import BattleResultCard from './BattleResultCard.vue';
import MovesCard from './MovesCard.vue';
import RunBattleDialog from './RunBattleDialog.vue';
import SendOutDialog from './SendOutDialog.vue';

const page = usePage<BattleShowPageProps>();

const titles: Record<BattleType, string> = {
  [BattleType.WILD]: 'Wild battle',
  [BattleType.TRAINER]: 'Trainer battle',
  [BattleType.GYM]: 'Gym battle',
  [BattleType.ELITE_FOUR]: 'Elite Four battle',
  [BattleType.BATTLE_TOWER]: 'Battle Tower battle',
};
</script>

<style scoped>
@reference '@css/app.css';

.battle-page__bottom {
  @apply flex flex-wrap items-stretch gap-4;
}

.battle-page__moves-card {
  @apply flex-[999_1_26rem];
}

.battle-page__log-card {
  @apply flex-[1_1_18rem];
}
</style>
