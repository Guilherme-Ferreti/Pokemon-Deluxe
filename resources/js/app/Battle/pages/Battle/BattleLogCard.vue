<template>
  <Card
    class="battle-log-card"
    data-test="battle-log-card"
  >
    <CardHeader>
      <CardTitle>Battle log</CardTitle>
    </CardHeader>
    <CardContent class="battle-log-card__content">
      <Collapsible
        v-for="turn in page.props.battle.log"
        :key="turn.turn"
        :open="isTurnOpen(turn.turn)"
        :data-test="`battle-log-card__turn-${turn.turn}-section`"
        @update:open="setTurnOpen(turn.turn, $event)"
      >
        <CollapsibleTrigger
          class="battle-log-card__turn-button"
          :data-test="`battle-log-card__turn-${turn.turn}-button`"
        >
          <IconChevronRight
            class="battle-log-card__chevron"
            aria-hidden="true"
          />
          {{ turnLabel(turn) }}
        </CollapsibleTrigger>
        <CollapsibleContent>
          <ol class="battle-log-card__list">
            <li
              v-for="(entry, index) in turn.entries"
              :key="index"
              :class="[
                'battle-log-card__line',
                `battle-log-card__line--${entry.kind}`,
                { 'battle-log-card__line--new': isNewLine(turn.turn, index) },
              ]"
              :style="{
                animationDelay: `${(index - (firstNewLine[turn.turn] ?? 0)) * LINE_DELAY_MILLISECONDS}ms`,
              }"
              :data-test="`battle-log-card__turn-${turn.turn}-line-${index + 1}`"
            >
              <template v-if="splitMove(entry)">
                {{ splitMove(entry)?.before
                }}<strong>{{ entry.moveName }}</strong
                >{{ splitMove(entry)?.after }}
              </template>
              <template v-else>{{ entry.text }}</template>
            </li>
          </ol>
        </CollapsibleContent>
      </Collapsible>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconChevronRight } from '@tabler/icons-vue';
import { reactive, watch } from 'vue';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/shared/components/ui/collapsible';
import { BattleLogKind } from '@/shared/types/enums';
import type {
  BattleLogTurn,
  BattleShowPageProps,
} from '../../types/page-props/BattleShowPageProps';

const LINE_DELAY_MILLISECONDS = 140;

const page = usePage<BattleShowPageProps>();

const chosenOpenStates = reactive<Record<number, boolean>>({});

const knownLineCounts: Record<number, number> = {};

const firstNewLine = reactive<Record<number, number>>({});

page.props.battle.log.forEach((turn) => {
  knownLineCounts[turn.turn] = turn.entries.length;
});

watch(
  () => page.props.battle.log,
  (log) => {
    log.forEach((turn) => {
      const known = knownLineCounts[turn.turn] ?? 0;

      if (turn.entries.length > known) {
        firstNewLine[turn.turn] = known;
        knownLineCounts[turn.turn] = turn.entries.length;
      }
    });
  },
);

function turnLabel(turn: BattleLogTurn): string {
  if (turn.isEnd) {
    return 'End';
  }

  return turn.turn === 0 ? 'Start' : `Turn ${turn.turn}`;
}

function isTurnOpen(turn: number): boolean {
  return chosenOpenStates[turn] ?? isOpenByDefault(turn);
}

function isOpenByDefault(turn: number): boolean {
  const [newest, previous] = page.props.battle.log;

  if (turn === newest?.turn) {
    return true;
  }

  return newest?.isEnd === true && turn === previous?.turn;
}

function setTurnOpen(turn: number, isOpen: boolean): void {
  chosenOpenStates[turn] = isOpen;
  delete firstNewLine[turn];
}

function isNewLine(turn: number, index: number): boolean {
  return index >= (firstNewLine[turn] ?? Number.POSITIVE_INFINITY);
}

function splitMove(
  entry: BattleLogTurn['entries'][number],
): { before: string; after: string } | null {
  if (entry.kind !== BattleLogKind.MOVE || entry.moveName === null) {
    return null;
  }

  const position = entry.text.indexOf(entry.moveName);

  if (position === -1) {
    return null;
  }

  return {
    before: entry.text.slice(0, position),
    after: entry.text.slice(position + entry.moveName.length),
  };
}
</script>

<style scoped>
@reference '@css/app.css';

.battle-log-card__content {
  @apply flex max-h-75 flex-col gap-1 overflow-y-auto md:max-h-117.5;
}

.battle-log-card__turn-button {
  @apply flex min-h-8 w-full items-center gap-1.5 border-t border-border text-left text-[11px] font-bold tracking-wider text-muted-foreground uppercase;
}

.battle-log-card__chevron {
  @apply size-4 transition-transform duration-200 motion-reduce:transition-none;
}

.battle-log-card__turn-button[data-state='open'] .battle-log-card__chevron {
  @apply rotate-90;
}

.battle-log-card__list {
  @apply flex flex-col gap-0.5 pb-1 pl-6;
}

.battle-log-card__line {
  @apply text-[13px] leading-normal;
}

.battle-log-card__line--damage,
.battle-log-card__line--detail {
  @apply text-xs text-muted-foreground;
}

.battle-log-card__line--info {
  @apply text-muted-foreground;
}

.battle-log-card__line--result {
  @apply font-semibold;
}

.battle-log-card__line--new {
  @apply animate-in duration-300 fill-mode-both fade-in slide-in-from-bottom-1 motion-reduce:animate-none;
}
</style>
