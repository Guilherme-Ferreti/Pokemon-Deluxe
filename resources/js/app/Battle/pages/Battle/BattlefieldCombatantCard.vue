<template>
  <div
    class="battlefield-combatant-card"
    :data-test="`battlefield-combatant-card--${side}`"
  >
    <div class="battlefield-combatant-card__details">
      <PokemonDisplayName
        class="battlefield-combatant-card__name"
        :data-test="`battlefield-combatant-card--${side}__name`"
        :name="combatant.name"
        :gender="combatant.gender"
      />
      <AilmentBadge
        v-if="combatant.ailment"
        :ailment="combatant.ailment"
      />
      <span class="battlefield-combatant-card__level">
        Lv {{ combatant.level }}
      </span>
    </div>
    <div class="battlefield-combatant-card__hp">
      <span
        class="battlefield-combatant-card__hp-label"
        aria-hidden="true"
      >
        HP:
      </span>
      <HpProgress
        class="battlefield-combatant-card__hp-bar"
        :percentage="hpPercentage"
        :label="`${combatant.name} HP`"
      />
      <span
        class="battlefield-combatant-card__hp-value"
        :data-test="`battlefield-combatant-card--${side}__hp-indicator`"
      >
        {{ combatant.currentHp }} / {{ combatant.maxHp }}
      </span>
    </div>
    <CombatantTeamDots
      v-if="side === 'opponent' && page.props.battle.type !== BattleType.WILD"
      class="battlefield-combatant-card__team-dots"
    />
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import { BattleType } from '@/shared/types/enums';
import type {
  BattleCombatant,
  BattleShowPageProps,
} from '../../types/page-props/BattleShowPageProps';
import AilmentBadge from './AilmentBadge.vue';
import CombatantTeamDots from './CombatantTeamDots.vue';
import HpProgress from './HpProgress.vue';

const props = defineProps<{
  side: 'player' | 'opponent';
}>();

const page = usePage<BattleShowPageProps>();

const combatant = computed<Omit<BattleCombatant, 'team'>>(
  () => page.props[props.side],
);

const hpPercentage = computed(() =>
  Math.ceil((combatant.value.currentHp / combatant.value.maxHp) * 100),
);
</script>

<style scoped>
@reference '@css/app.css';

.battlefield-combatant-card {
  @apply flex min-w-0 flex-1 flex-col gap-1.5 rounded-lg border border-border bg-card/95 px-3 py-2.5 @sm/battlefield:w-60 @sm/battlefield:flex-none;
}

.battlefield-combatant-card__details {
  @apply flex flex-wrap items-center gap-1.5;
}

.battlefield-combatant-card__name {
  @apply font-semibold;
}

.battlefield-combatant-card__level {
  @apply ml-auto text-xs font-medium whitespace-nowrap text-muted-foreground tabular-nums @sm/battlefield:text-sm;
}

.battlefield-combatant-card__hp {
  @apply flex items-center gap-1.5;
}

.battlefield-combatant-card__hp-label {
  @apply text-xs font-semibold text-muted-foreground @sm/battlefield:text-sm;
}

.battlefield-combatant-card__hp-bar {
  @apply h-2;
}

.battlefield-combatant-card__hp-value {
  @apply text-xs font-semibold whitespace-nowrap tabular-nums @sm/battlefield:text-sm;
}

.battlefield-combatant-card__team-dots {
  @apply @5xl/main-content:hidden;
}
</style>
