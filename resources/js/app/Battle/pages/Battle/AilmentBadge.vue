<template>
  <Tooltip>
    <TooltipTrigger as-child>
      <Badge
        :class="['px-1 py-px text-xxs', ailmentColors[ailment]]"
        :aria-label="$page.props.labels.ailment[ailment]"
      >
        {{ ailmentCodes[ailment] }}
      </Badge>
    </TooltipTrigger>
    <TooltipContent>
      {{ $page.props.labels.ailment[ailment] }}
    </TooltipContent>
  </Tooltip>
</template>

<script setup lang="ts">
import { Badge } from '@/shared/components/ui/badge';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import { Ailment } from '@/shared/types/enums';
import type { BattleCombatant } from '../../types/page-props/BattleShowPageProps';

defineProps<{
  ailment: NonNullable<BattleCombatant['ailment']>;
}>();

const ailmentCodes: Record<NonNullable<BattleCombatant['ailment']>, string> = {
  [Ailment.BURN]: 'BRN',
  [Ailment.FREEZE]: 'FRZ',
  [Ailment.PARALYSIS]: 'PAR',
  [Ailment.POISON]: 'PSN',
  [Ailment.SLEEP]: 'SLP',
};

const ailmentColors: Record<NonNullable<BattleCombatant['ailment']>, string> = {
  [Ailment.BURN]:
    'border-orange-300 bg-orange-100 text-orange-800 dark:border-orange-700 dark:bg-orange-950 dark:text-orange-300',
  [Ailment.FREEZE]:
    'border-cyan-300 bg-cyan-100 text-cyan-800 dark:border-cyan-700 dark:bg-cyan-950 dark:text-cyan-300',
  [Ailment.PARALYSIS]:
    'border-yellow-300 bg-yellow-100 text-yellow-800 dark:border-yellow-700 dark:bg-yellow-950 dark:text-yellow-300',
  [Ailment.POISON]:
    'border-purple-300 bg-purple-100 text-purple-800 dark:border-purple-700 dark:bg-purple-950 dark:text-purple-300',
  [Ailment.SLEEP]:
    'border-slate-300 bg-slate-100 text-slate-800 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300',
};
</script>
