<template>
  <ul
    class="combatant-team-dots"
    :aria-label="summary"
  >
    <li
      v-for="(member, index) in team"
      :key="index"
    >
      <Tooltip>
        <TooltipTrigger as-child>
          <span
            tabindex="0"
            :class="[
              'combatant-team-dots__dot',
              { 'combatant-team-dots__dot--fainted': member.isFainted },
            ]"
            :aria-label="label(member)"
          ></span>
        </TooltipTrigger>
        <TooltipContent>{{ label(member) }}</TooltipContent>
      </Tooltip>
    </li>
  </ul>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import type {
  BattleOpponentTeamMember,
  BattleShowPageProps,
} from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

const team = computed(() => page.props.opponent.team);

const summary = computed(() => {
  const standing = team.value.filter((member) => !member.isFainted).length;

  return `${standing} of ${team.value.length} Pokémon can still fight`;
});

function label(member: BattleOpponentTeamMember): string {
  if (!member.isRevealed) {
    return 'Not sent out yet';
  }

  const name = member.name ?? '';

  return member.isFainted ? `${name} (fainted)` : name;
}
</script>

<style scoped>
@reference '@css/app.css';

.combatant-team-dots {
  @apply flex gap-1;
}

.combatant-team-dots__dot {
  @apply block size-2.5 rounded-full border-2 border-green-600 bg-green-600 dark:border-green-500 dark:bg-green-500;
}

.combatant-team-dots__dot--fainted {
  @apply border-muted-foreground/50 bg-transparent dark:border-muted-foreground/50 dark:bg-transparent;
}
</style>
