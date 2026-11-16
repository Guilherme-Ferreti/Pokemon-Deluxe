<template>
  <div
    :class="[
      'combatant-panel',
      { 'combatant-panel--shiny': combatant.isShiny },
    ]"
  >
    <div class="combatant-panel__details">
      <PokemonDisplayName
        class="combatant-panel__name"
        :name="combatant.name"
        :gender="combatant.gender"
      />
      <Badge variant="secondary">Lv {{ combatant.level }}</Badge>
      <Badge
        v-if="combatant.boost > 0"
        variant="secondary"
      >
        +{{ combatant.boost }}
      </Badge>
      <Tooltip v-if="combatant.ailment">
        <TooltipTrigger as-child>
          <Badge
            class="combatant-panel__ailment-badge"
            :aria-label="$page.props.labels.ailment[combatant.ailment]"
          >
            {{ ailmentCodes[combatant.ailment] }}
          </Badge>
        </TooltipTrigger>
        <TooltipContent>
          {{ $page.props.labels.ailment[combatant.ailment] }}
        </TooltipContent>
      </Tooltip>
    </div>
    <div class="combatant-panel__hp">
      <span
        class="combatant-panel__hp-label"
        aria-hidden="true"
      >
        HP
      </span>
      <Progress
        :class="['combatant-panel__hp-bar', hpBarColor]"
        :model-value="hpPercentage"
        :aria-label="`${combatant.name} HP`"
      />
      <span
        class="combatant-panel__hp-value"
        :data-test="`combatant-panel--${side}__hp-indicator`"
      >
        {{ hpText }}
      </span>
    </div>
    <CombatantTeamDots :side="side" />
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Progress } from '@/shared/components/ui/progress';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import { Ailment } from '@/shared/types/enums';
import type {
  BattleCombatant,
  BattleShowPageProps,
} from '../../types/page-props/BattleShowPageProps';
import CombatantTeamDots from './CombatantTeamDots.vue';

const props = defineProps<{
  side: 'player' | 'opponent';
}>();

const page = usePage<BattleShowPageProps>();

const ailmentCodes: Record<NonNullable<BattleCombatant['ailment']>, string> = {
  [Ailment.BURN]: 'BRN',
  [Ailment.FREEZE]: 'FRZ',
  [Ailment.PARALYSIS]: 'PAR',
  [Ailment.POISON]: 'PSN',
  [Ailment.SLEEP]: 'SLP',
};

const combatant = computed<BattleCombatant>(() => page.props[props.side]);

const hpPercentage = computed(() =>
  props.side === 'player'
    ? Math.ceil((page.props.player.currentHp / page.props.player.maxHp) * 100)
    : page.props.opponent.hpPercentage,
);

const hpText = computed(() =>
  props.side === 'player'
    ? `${page.props.player.currentHp} / ${page.props.player.maxHp}`
    : `${page.props.opponent.hpPercentage}%`,
);

const hpBarColor = computed(() => {
  if (hpPercentage.value > 50) {
    return 'combatant-panel__hp-bar--good';
  }

  return hpPercentage.value >= 20
    ? 'combatant-panel__hp-bar--warning'
    : 'combatant-panel__hp-bar--danger';
});
</script>

<style scoped>
@reference '@css/app.css';

.combatant-panel {
  @apply flex max-w-80 min-w-0 flex-[1_1_15rem] flex-col gap-2 rounded-lg border border-border p-3;
}

.combatant-panel--shiny {
  @apply border-amber-400 ring-2 ring-amber-400/40 dark:border-amber-500 dark:ring-amber-500/40;
}

.combatant-panel__details {
  @apply flex flex-wrap items-center gap-1;
}

.combatant-panel__name {
  @apply mr-1 font-semibold;
}

.combatant-panel__ailment-badge {
  @apply border-yellow-300 bg-yellow-100 text-yellow-800 dark:border-yellow-700 dark:bg-yellow-950 dark:text-yellow-300;
}

.combatant-panel__hp {
  @apply flex items-center gap-2;
}

.combatant-panel__hp-label {
  @apply text-xs font-bold text-muted-foreground;
}

.combatant-panel__hp-bar {
  @apply min-w-0 flex-1;
}

.combatant-panel__hp-bar :deep([data-slot='progress-indicator']) {
  @apply duration-500 ease-out motion-reduce:transition-none;
}

.combatant-panel__hp-bar--good :deep([data-slot='progress-indicator']) {
  @apply bg-green-600 dark:bg-green-500;
}

.combatant-panel__hp-bar--warning :deep([data-slot='progress-indicator']) {
  @apply bg-yellow-500 dark:bg-yellow-400;
}

.combatant-panel__hp-bar--danger :deep([data-slot='progress-indicator']) {
  @apply bg-destructive;
}

.combatant-panel__hp-value {
  @apply min-w-10 text-right text-sm font-semibold whitespace-nowrap tabular-nums;
}
</style>
