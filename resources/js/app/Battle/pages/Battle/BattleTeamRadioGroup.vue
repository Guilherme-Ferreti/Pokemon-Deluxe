<template>
  <RadioGroup
    v-model="combatantIndex"
    :class="[
      'battle-team-radio-group',
      { 'battle-team-radio-group--tiled': isTiledOnPhones },
    ]"
    :disabled="disabled"
  >
    <FieldLabel
      v-for="(member, index) in page.props.player.team"
      :key="index"
      :for="`${idPrefix}-${index}`"
      :class="[
        'battle-team-radio-group__member',
        { 'battle-team-radio-group__member--fainted': member.isFainted },
      ]"
      :data-test="`${testIdBlock}__pokemon-${index + 1}-radio`"
    >
      <RadioGroupItem
        :id="`${idPrefix}-${index}`"
        class="battle-team-radio-group__radio"
        :value="String(index)"
        :disabled="disabled || member.isFainted"
      />
      <SpeciesPortrait
        class="battle-team-radio-group__portrait"
        :url="member.portrait"
        :name="member.name"
      />
      <span class="battle-team-radio-group__body">
        <span class="battle-team-radio-group__name-row">
          <PokemonDisplayName
            class="battle-team-radio-group__name"
            :name="member.name"
            :gender="member.gender"
          />
          <Badge
            v-if="member.isFainted"
            variant="secondary"
            class="battle-team-radio-group__fainted-tag"
          >
            Fainted
          </Badge>
        </span>
        <span class="battle-team-radio-group__meta">
          Lv {{ member.level
          }}{{ member.boost > 0 ? ` · +${member.boost}` : '' }}
          <AilmentBadge
            v-if="member.ailment && !member.isFainted"
            :ailment="member.ailment"
          />
        </span>
        <span
          v-if="!isInBattle(member)"
          class="battle-team-radio-group__hp"
        >
          <span
            class="battle-team-radio-group__hp-label"
            aria-hidden="true"
          >
            HP:
          </span>
          <HpProgress
            class="battle-team-radio-group__hp-bar"
            :percentage="Math.ceil((member.currentHp / member.maxHp) * 100)"
            :label="`${member.name} HP`"
          />
          <span class="battle-team-radio-group__hp-value">
            {{ member.currentHp }} / {{ member.maxHp }}
          </span>
        </span>
      </span>
      <template v-if="isInBattle(member)">
        <IconPokeball
          class="battle-team-radio-group__in-battle-icon"
          aria-hidden="true"
        />
        <span class="sr-only">In battle</span>
      </template>
    </FieldLabel>
  </RadioGroup>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconPokeball } from '@tabler/icons-vue';
import { useId } from 'vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { Badge } from '@/shared/components/ui/badge';
import { FieldLabel } from '@/shared/components/ui/field';
import { RadioGroup, RadioGroupItem } from '@/shared/components/ui/radio-group';
import type {
  BattleShowPageProps,
  BattleTeamMember,
} from '../../types/page-props/BattleShowPageProps';
import AilmentBadge from './AilmentBadge.vue';
import HpProgress from './HpProgress.vue';

const props = withDefaults(
  defineProps<{
    testIdBlock: string;
    disabled?: boolean;
    isTiledOnPhones?: boolean;
    marksPokemonInBattle?: boolean;
  }>(),
  {
    disabled: false,
    isTiledOnPhones: false,
    marksPokemonInBattle: false,
  },
);

const combatantIndex = defineModel<string | null>({ required: true });

const page = usePage<BattleShowPageProps>();

const idPrefix = useId();

function isInBattle(member: BattleTeamMember): boolean {
  return props.marksPokemonInBattle && member.isActive && !member.isFainted;
}
</script>

<style scoped>
@reference '@css/app.css';

.battle-team-radio-group {
  @apply flex flex-col gap-1.5;
}

.battle-team-radio-group__member {
  @apply flex min-h-14 w-full cursor-pointer items-center gap-2.5 rounded-lg border border-border bg-card px-2.5 py-2 font-normal transition-colors duration-250 has-focus-visible:ring-[3px] has-focus-visible:ring-ring/50 has-data-disabled:cursor-default motion-reduce:transition-none;
}

.battle-team-radio-group__member:has([data-state='checked']) {
  @apply min-h-16 border-muted-foreground/40 bg-muted;
}

.battle-team-radio-group__member--fainted {
  @apply opacity-50;
}

.battle-team-radio-group__member--fainted .battle-team-radio-group__portrait {
  @apply grayscale;
}

.battle-team-radio-group__member :deep(.battle-team-radio-group__radio) {
  @apply sr-only;
}

.battle-team-radio-group__portrait {
  @apply size-9 shrink-0;
}

.battle-team-radio-group__body {
  @apply flex min-w-0 flex-1 flex-col gap-0.5;
}

.battle-team-radio-group__name-row {
  @apply flex items-start gap-1.5 text-sm;
}

.battle-team-radio-group__name {
  @apply min-w-0 flex-1 leading-snug font-semibold wrap-anywhere;
}

.battle-team-radio-group__meta {
  @apply flex items-center gap-1.5 text-xs text-muted-foreground;
}

.battle-team-radio-group__hp {
  @apply flex items-center gap-1.5;
}

.battle-team-radio-group__hp-label {
  @apply text-xs font-semibold text-muted-foreground;
}

.battle-team-radio-group__hp-bar {
  @apply h-1.5 min-w-10;
}

.battle-team-radio-group__hp-value {
  @apply text-xs whitespace-nowrap text-muted-foreground tabular-nums;
}

.battle-team-radio-group__in-battle-icon {
  @apply size-5 shrink-0;
}

.battle-team-radio-group--tiled {
  @apply grid grid-cols-1 @md/main-content:grid-cols-2 @2xl/main-content:grid-cols-3 @5xl/main-content:flex;
}

.battle-team-radio-group--tiled .battle-team-radio-group__member {
  @apply min-h-24 flex-col justify-center gap-1 px-1.5 py-2 text-center @5xl/main-content:min-h-14 @5xl/main-content:flex-row @5xl/main-content:justify-start @5xl/main-content:gap-2.5 @5xl/main-content:px-2.5 @5xl/main-content:text-left;
}

.battle-team-radio-group--tiled .battle-team-radio-group__portrait {
  @apply size-10 @5xl/main-content:size-9;
}

.battle-team-radio-group--tiled .battle-team-radio-group__body {
  @apply w-full items-center @5xl/main-content:w-auto @5xl/main-content:items-stretch;
}

.battle-team-radio-group--tiled .battle-team-radio-group__name-row {
  @apply justify-center text-xs @5xl/main-content:justify-start @5xl/main-content:text-sm;
}

.battle-team-radio-group--tiled .battle-team-radio-group__name {
  @apply flex-none @5xl/main-content:flex-1;
}

.battle-team-radio-group--tiled .battle-team-radio-group__hp {
  @apply w-full;
}
</style>
