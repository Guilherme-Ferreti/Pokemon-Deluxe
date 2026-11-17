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
          v-for="(entry, index) in entries"
          :key="index"
          class="battle-log-card__line"
        >
          {{ entry.text }}
        </li>
      </ol>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, useTemplateRef, watch } from 'vue';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

const entries = computed(() =>
  page.props.battle.log.toReversed().flatMap((turn) => turn.entries),
);

const logList = useTemplateRef('logList');

function scrollToNewestLine(): void {
  if (logList.value) {
    logList.value.scrollTop = logList.value.scrollHeight;
  }
}

onMounted(scrollToNewestLine);

watch(
  () => entries.value.length,
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
