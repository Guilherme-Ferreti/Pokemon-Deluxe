<template>
  <span class="flex min-w-0 flex-1 flex-col gap-0.5">
    <span class="flex items-start gap-1.5 text-sm">
      <span class="min-w-0 flex-1 leading-snug font-semibold wrap-anywhere">
        <PokemonDisplayName
          :name="name"
          :gender="gender"
        />
        <span
          v-if="ailment"
          class="ml-1 inline-flex align-middle"
        >
          <AilmentBadge :ailment="ailment" />
        </span>
      </span>
      <span
        v-if="level !== null"
        class="text-xs leading-5 whitespace-nowrap tabular-nums"
        :class="secondaryTextClass"
      >
        Lv {{ level }}
      </span>
      <Badge
        v-if="hasFaintedTag"
        variant="secondary"
      >
        Fainted
      </Badge>
    </span>
    <span
      v-if="currentHp !== null && maxHp !== null"
      class="flex items-center gap-1.5"
    >
      <span
        class="text-xs font-semibold"
        aria-hidden="true"
        :class="secondaryTextClass"
      >
        HP:
      </span>
      <HpProgress
        class="h-1.5 min-w-10"
        :percentage="Math.ceil((currentHp / maxHp) * 100)"
        :label="`${name} HP`"
      />
      <span
        class="text-xs whitespace-nowrap tabular-nums"
        :class="secondaryTextClass"
      >
        {{ currentHp }} / {{ maxHp }}
      </span>
    </span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import { Badge } from '@/shared/components/ui/badge';
import type {
  BattleCombatant,
  BattleTeamMember,
} from '../../types/page-props/BattleShowPageProps';
import AilmentBadge from './AilmentBadge.vue';
import HpProgress from './HpProgress.vue';

const props = withDefaults(
  defineProps<{
    name: BattleTeamMember['name'];
    gender: BattleTeamMember['gender'];
    level?: BattleTeamMember['level'] | null;
    ailment?: BattleCombatant['ailment'];
    hasFaintedTag?: boolean;
    currentHp?: number | null;
    maxHp?: number | null;
    isHighlighted?: boolean;
  }>(),
  {
    level: null,
    ailment: null,
    hasFaintedTag: false,
    currentHp: null,
    maxHp: null,
    isHighlighted: false,
  },
);

const secondaryTextClass = computed(() =>
  props.isHighlighted ? 'text-foreground/75' : 'text-muted-foreground',
);
</script>
