<template>
  <div
    class="flex w-full min-w-0 flex-1 flex-col gap-1.5 rounded-lg border border-border bg-card/95 px-3 py-2.5 @sm/battlefield:max-w-60 @sm/battlefield:flex-none"
    :data-test="`battlefield-combatant-card--${side}`"
  >
    <div class="flex items-start gap-1.5">
      <PokemonDisplayName
        class="text-sm font-semibold"
        :data-test="`battlefield-combatant-card--${side}__name`"
        :name="combatant.name"
        :gender="combatant.gender"
      />
      <AilmentBadge
        v-if="combatant.ailment"
        :ailment="combatant.ailment"
      />
      <span
        class="ml-auto text-xs font-medium whitespace-nowrap text-muted-foreground tabular-nums"
      >
        Lv {{ combatant.level }}
      </span>
    </div>
    <div class="flex items-center gap-1.5">
      <span
        class="text-xs font-semibold text-muted-foreground"
        aria-hidden="true"
      >
        HP:
      </span>
      <HpProgress
        class="h-2"
        :percentage="hpPercentage"
        :label="`${combatant.name} HP`"
      />
      <span
        class="text-xs font-semibold whitespace-nowrap tabular-nums"
        :data-test="`battlefield-combatant-card--${side}__hp-indicator`"
      >
        {{ combatant.currentHp }} / {{ combatant.maxHp }}
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import type {
  BattleCombatant,
  BattleShowPageProps,
} from '../../types/page-props/BattleShowPageProps';
import AilmentBadge from './AilmentBadge.vue';
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
