<template>
  <Tooltip>
    <TooltipTrigger as-child>
      <Badge
        class="ailment-badge"
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
</script>

<style scoped>
@reference '@css/app.css';

.ailment-badge {
  @apply border-yellow-300 bg-yellow-100 text-yellow-800 dark:border-yellow-700 dark:bg-yellow-950 dark:text-yellow-300;
}
</style>
