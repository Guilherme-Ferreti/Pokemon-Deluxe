<template>
  <Card
    class="battle-log-card"
    data-test="battle-log-card"
  >
    <CardHeader>
      <CardTitle class="battle-log-card__title">
        Battle log
        <Tooltip>
          <TooltipTrigger as-child>
            <button
              type="button"
              class="battle-log-card__info-button"
              aria-label="About the Battle log"
              data-test="battle-log-card__info-button"
            >
              <IconInfoCircle
                class="size-4"
                aria-hidden="true"
              />
            </button>
          </TooltipTrigger>
          <TooltipContent class="battle-log-card__tooltip">
            The newest turn is at the top and open; older turns are collapsed.
            Click a turn to open or close it.
          </TooltipContent>
        </Tooltip>
      </CardTitle>
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
          {{ turn.turn === 0 ? 'Start' : `Turn ${turn.turn}` }}
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
import { IconChevronRight, IconInfoCircle } from '@tabler/icons-vue';
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
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
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

function isTurnOpen(turn: number): boolean {
  return chosenOpenStates[turn] ?? turn === page.props.battle.log[0]?.turn;
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

.battle-log-card__title {
  @apply flex items-center gap-2;
}

.battle-log-card__info-button {
  @apply rounded-full text-muted-foreground outline-none hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50;
}

.battle-log-card__tooltip {
  @apply max-w-64;
}

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
  @apply flex flex-col gap-0.5 pb-1;
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

.battle-log-card__line--new {
  @apply animate-in duration-300 fill-mode-both fade-in slide-in-from-bottom-1 motion-reduce:animate-none;
}
</style>
