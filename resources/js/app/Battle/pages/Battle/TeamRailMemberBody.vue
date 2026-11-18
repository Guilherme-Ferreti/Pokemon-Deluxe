<template>
  <span class="team-rail-member-body">
    <span class="team-rail-member-body__name-row">
      <span class="team-rail-member-body__name">
        <PokemonDisplayName
          :name="name"
          :gender="gender"
        />
        <span
          v-if="ailment"
          class="team-rail-member-body__ailment"
        >
          <AilmentBadge :ailment="ailment" />
        </span>
      </span>
      <span
        v-if="level !== null"
        class="team-rail-member-body__level"
      >
        Lv {{ level }}
      </span>
      <Badge
        v-if="hasFaintedTag"
        variant="secondary"
        class="team-rail-member-body__fainted-tag"
      >
        Fainted
      </Badge>
    </span>
    <span
      v-if="currentHp !== null && maxHp !== null"
      class="team-rail-member-body__hp"
    >
      <span
        class="team-rail-member-body__hp-label"
        aria-hidden="true"
      >
        HP:
      </span>
      <HpProgress
        class="team-rail-member-body__hp-bar"
        :percentage="Math.ceil((currentHp / maxHp) * 100)"
        :label="`${name} HP`"
      />
      <span class="team-rail-member-body__hp-value">
        {{ currentHp }} / {{ maxHp }}
      </span>
    </span>
  </span>
</template>

<script setup lang="ts">
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import { Badge } from '@/shared/components/ui/badge';
import type {
  BattleCombatant,
  BattleTeamMember,
} from '../../types/page-props/BattleShowPageProps';
import AilmentBadge from './AilmentBadge.vue';
import HpProgress from './HpProgress.vue';

withDefaults(
  defineProps<{
    name: BattleTeamMember['name'];
    gender: BattleTeamMember['gender'];
    level?: BattleTeamMember['level'] | null;
    ailment?: BattleCombatant['ailment'];
    hasFaintedTag?: boolean;
    currentHp?: number | null;
    maxHp?: number | null;
  }>(),
  {
    level: null,
    ailment: null,
    hasFaintedTag: false,
    currentHp: null,
    maxHp: null,
  },
);
</script>

<style scoped>
@reference '@css/app.css';

.team-rail-member-body {
  @apply flex min-w-0 flex-1 flex-col gap-0.5;
}

.team-rail-member-body__name-row {
  @apply flex items-start gap-1.5 text-sm;
}

.team-rail-member-body__name {
  @apply min-w-0 flex-1 leading-snug font-semibold wrap-anywhere;
}

.team-rail-member-body__ailment {
  @apply ml-1 inline-flex align-middle;
}

.team-rail-member-body__level {
  @apply text-xs leading-5 whitespace-nowrap text-muted-foreground tabular-nums;
}

.team-rail-member-body__hp {
  @apply flex items-center gap-1.5;
}

.team-rail-member-body__hp-label {
  @apply text-xs font-semibold text-muted-foreground;
}

.team-rail-member-body__hp-bar {
  @apply h-1.5 min-w-10;
}

.team-rail-member-body__hp-value {
  @apply text-xs whitespace-nowrap text-muted-foreground tabular-nums;
}
</style>
