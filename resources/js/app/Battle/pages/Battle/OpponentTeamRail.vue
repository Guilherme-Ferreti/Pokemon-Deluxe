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
    <CardContent>
      <ul class="opponent-team-rail__list">
        <li
          v-for="(member, index) in page.props.opponent.team"
          :key="index"
          :class="[
            'opponent-team-rail__member',
            {
              'opponent-team-rail__member--unrevealed': !member.isRevealed,
              'opponent-team-rail__member--fainted': member.isFainted,
            },
          ]"
          :data-test="`opponent-team-rail__pokemon-${index + 1}-item`"
        >
          <template v-if="member.isRevealed">
            <SpeciesPortrait
              class="opponent-team-rail__portrait"
              :url="member.portrait"
              :name="member.name ?? ''"
            />
            <span class="opponent-team-rail__body">
              <PokemonDisplayName
                class="opponent-team-rail__name"
                :name="member.name ?? ''"
                :gender="member.gender ?? PokemonGender.GENDERLESS"
              />
              <span class="opponent-team-rail__meta">
                Lv {{ member.level
                }}{{ member.boost ? ` · +${member.boost}` : '' }}
              </span>
              <span
                v-if="!member.isActive || member.isFainted"
                class="opponent-team-rail__hp"
              >
                <template v-if="member.isFainted">
                  <span class="opponent-team-rail__hp-value">Fainted</span>
                </template>
                <template v-else>
                  <span
                    class="opponent-team-rail__hp-label"
                    aria-hidden="true"
                  >
                    HP:
                  </span>
                  <HpProgress
                    class="opponent-team-rail__hp-bar"
                    :percentage="
                      Math.ceil(
                        ((member.currentHp ?? 0) / (member.maxHp ?? 1)) * 100,
                      )
                    "
                    :label="`${member.name} HP`"
                  />
                  <span class="opponent-team-rail__hp-value">
                    {{ member.currentHp }} / {{ member.maxHp }}
                  </span>
                </template>
              </span>
            </span>
            <template v-if="member.isActive && !member.isFainted">
              <IconPokeball
                class="opponent-team-rail__in-battle-icon"
                aria-hidden="true"
              />
              <span class="sr-only">In battle</span>
            </template>
          </template>
          <span
            v-else
            class="opponent-team-rail__unrevealed-label"
          >
            Not sent out yet
          </span>
        </li>
      </ul>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconInfoCircle, IconPokeball } from '@tabler/icons-vue';
import { computed } from 'vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
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
import { BattleType, PokemonGender } from '@/shared/types/enums';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import HpProgress from './HpProgress.vue';

const page = usePage<BattleShowPageProps>();

const hint = computed(() =>
  [BattleType.GYM, BattleType.ELITE_FOUR].includes(page.props.battle.type)
    ? "Pokémon are revealed as they're sent out."
    : 'The whole team is shown from the start.',
);
</script>

<style scoped>
@reference '@css/app.css';

.opponent-team-rail__title {
  @apply flex items-center gap-1.5;
}

.opponent-team-rail__info-button {
  @apply rounded-full text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none motion-reduce:transition-none;
}

.opponent-team-rail__list {
  @apply flex flex-col gap-1.5;
}

.opponent-team-rail__member {
  @apply flex min-h-18.5 items-center gap-2.5 rounded-lg border border-border bg-card px-2.5 py-2;
}

.opponent-team-rail__member--unrevealed {
  @apply border-dashed bg-muted;
}

.opponent-team-rail__member--fainted {
  @apply opacity-50;
}

.opponent-team-rail__member--fainted .opponent-team-rail__portrait {
  @apply grayscale;
}

.opponent-team-rail__portrait {
  @apply size-9 shrink-0;
}

.opponent-team-rail__body {
  @apply flex min-w-0 flex-1 flex-col gap-0.5;
}

.opponent-team-rail__name {
  @apply text-sm leading-snug font-semibold wrap-anywhere;
}

.opponent-team-rail__meta {
  @apply text-xs text-muted-foreground;
}

.opponent-team-rail__hp {
  @apply flex items-center gap-1.5;
}

.opponent-team-rail__hp-label {
  @apply text-xs font-semibold text-muted-foreground;
}

.opponent-team-rail__hp-bar {
  @apply h-1.5 min-w-10;
}

.opponent-team-rail__hp-value {
  @apply text-xs whitespace-nowrap text-muted-foreground tabular-nums;
}

.opponent-team-rail__in-battle-icon {
  @apply size-5 shrink-0;
}

.opponent-team-rail__unrevealed-label {
  @apply text-sm font-semibold text-muted-foreground;
}
</style>
