<template>
  <Card
    class="opponent-team-rail"
    data-test="opponent-team-rail"
  >
    <CardHeader>
      <CardTitle class="opponent-team-rail__title">
        Opponent's team
        <Tooltip>
          <TooltipTrigger as-child>
            <button
              type="button"
              class="opponent-team-rail__info-button"
              aria-label="About the opponent's team"
              data-test="opponent-team-rail__info-button"
            >
              <IconInfoCircle
                class="size-4"
                aria-hidden="true"
              />
            </button>
          </TooltipTrigger>
          <TooltipContent>{{ hint }}</TooltipContent>
        </Tooltip>
      </CardTitle>
    </CardHeader>
    <CardContent class="opponent-team-rail__content">
      <TeamRailGroup
        v-for="group in groups"
        :key="group.heading"
        :heading="group.heading"
        :data-test="`opponent-team-rail__${group.testId}-list`"
      >
        <li
          v-for="{ member, index } in group.members"
          :key="index"
          :class="[
            'opponent-team-rail__member',
            {
              'opponent-team-rail__member--in-battle': member.isActive,
              'opponent-team-rail__member--unrevealed': !member.isRevealed,
              'opponent-team-rail__member--fainted': member.isFainted,
            },
          ]"
          :data-test="`opponent-team-rail__pokemon-${index + 1}-item`"
        >
          <template v-if="member.isRevealed">
            <PokemonDetailsButton
              :pokemon="member"
              :is-fainted="member.isFainted"
              :test-id="`opponent-team-rail__pokemon-${index + 1}-details-button`"
            />
            <TeamRailMemberBody
              class="opponent-team-rail__body"
              :name="member.name"
              :gender="member.gender"
              :level="member.isActive ? null : member.level"
              :has-fainted-tag="member.isFainted"
              :current-hp="hasHpRow(member) ? member.currentHp : null"
              :max-hp="hasHpRow(member) ? member.maxHp : null"
            />
          </template>
          <template v-else>
            <span
              class="opponent-team-rail__unrevealed-portrait"
              aria-hidden="true"
            />
            <span class="opponent-team-rail__unrevealed-label">
              Not sent out yet
            </span>
          </template>
        </li>
      </TeamRailGroup>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconInfoCircle } from '@tabler/icons-vue';
import { computed } from 'vue';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import { BattleType } from '@/shared/types/enums';
import type {
  BattleOpponentTeamMember,
  BattleShowPageProps,
} from '../../types/page-props/BattleShowPageProps';
import PokemonDetailsButton from './PokemonDetailsButton.vue';
import TeamRailGroup from './TeamRailGroup.vue';
import TeamRailMemberBody from './TeamRailMemberBody.vue';

const page = usePage<BattleShowPageProps>();

const hint = computed(() =>
  [BattleType.GYM, BattleType.ELITE_FOUR].includes(page.props.battle.type)
    ? "Pokémon are revealed as they're sent out."
    : 'The whole team is shown from the start.',
);

const groups = computed(() => {
  const team = page.props.opponent.team.map((member, index) => ({
    member,
    index,
  }));

  return [
    {
      heading: 'In battle',
      testId: 'in-battle',
      members: team.filter(({ member }) => member.isActive),
    },
    {
      heading: 'Rest of the team',
      testId: 'rest-of-the-team',
      members: team.filter(({ member }) => !member.isActive),
    },
  ].filter((group) => group.members.length > 0);
});

/**
 * The battlefield shows the HP of the one in battle, and a fainted one only needs its "Fainted" tag.
 */
function hasHpRow(member: BattleOpponentTeamMember): boolean {
  return !member.isActive && !member.isFainted;
}
</script>

<style scoped>
@reference '@css/app.css';

.opponent-team-rail__title {
  @apply flex items-center gap-1.5;
}

.opponent-team-rail__info-button {
  @apply rounded-full text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none motion-reduce:transition-none;
}

.opponent-team-rail__content {
  @apply flex flex-col gap-3;
}

.opponent-team-rail__member {
  @apply flex min-h-16 items-center gap-2 rounded-lg border border-border bg-card py-2 pr-2.5 pl-2;
}

.opponent-team-rail__member--in-battle {
  @apply min-h-0;
}

.opponent-team-rail__member--unrevealed {
  @apply min-h-14.5 border-dashed bg-muted;
}

.opponent-team-rail__member--fainted .opponent-team-rail__body {
  @apply opacity-50;
}

.opponent-team-rail__unrevealed-portrait {
  @apply m-0.5 size-9 shrink-0;
}

.opponent-team-rail__unrevealed-label {
  @apply text-sm font-semibold text-muted-foreground;
}
</style>
