<template>
  <Card
    class="battle-options-card"
    data-test="battle-options-card"
  >
    <CardHeader>
      <CardTitle>More options</CardTitle>
    </CardHeader>
    <CardContent class="battle-options-card__content">
      <BattleThrowBallButton
        v-if="page.props.battle.type === BattleType.WILD"
      />
      <BattleForfeitButton />
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import { BattleType } from '@/shared/types/enums';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BattleForfeitButton from './BattleForfeitButton.vue';
import BattleThrowBallButton from './BattleThrowBallButton.vue';

const page = usePage<BattleShowPageProps>();
</script>

<style scoped>
@reference '@css/app.css';

.battle-options-card__content {
  @apply flex flex-wrap gap-2;
}
</style>
