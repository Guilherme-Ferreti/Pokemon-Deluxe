<template>
  <Card
    class="battle-log-card"
    data-test="battle-log-card"
  >
    <CardHeader>
      <CardTitle>Battle log</CardTitle>
    </CardHeader>
    <CardContent>
      <ol
        ref="logList"
        class="battle-log-card__list"
        aria-live="polite"
      >
        <li
          v-for="(line, index) in page.props.battle.log"
          :key="index"
          class="battle-log-card__line"
        >
          {{ line }}
        </li>
      </ol>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { nextTick, onMounted, useTemplateRef, watch } from 'vue';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

const logList = useTemplateRef('logList');

function scrollToNewestLine(): void {
  if (logList.value) {
    logList.value.scrollTop = logList.value.scrollHeight;
  }
}

onMounted(scrollToNewestLine);

watch(
  () => page.props.battle.log.length,
  async () => {
    await nextTick();
    scrollToNewestLine();
  },
);
</script>

<style scoped>
@reference '@css/app.css';

.battle-log-card__list {
  @apply flex h-56 flex-col gap-1.5 overflow-y-auto pr-1;
}

.battle-log-card__line {
  @apply text-sm;
}
</style>
