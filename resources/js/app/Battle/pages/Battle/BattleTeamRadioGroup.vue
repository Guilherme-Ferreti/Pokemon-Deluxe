<template>
  <RadioGroup
    v-model="combatantIndex"
    aria-label="Bench"
    :class="[
      'battle-team-radio-group',
      { 'battle-team-radio-group--grid': isGridBelowWideLayout },
    ]"
    :disabled="disabled"
  >
    <div
      v-for="{ member, index } in bench"
      :key="index"
      :class="[
        'battle-team-radio-group__member',
        { 'battle-team-radio-group__member--fainted': member.isFainted },
      ]"
    >
      <PokemonDetailsButton
        v-if="hasDetailsButtons"
        :pokemon="member"
        :is-fainted="member.isFainted"
        :test-id="`${testIdBlock}__pokemon-${index + 1}-details-button`"
      />
      <SpeciesPortrait
        v-else
        class="battle-team-radio-group__portrait"
        :url="member.portrait"
        :name="member.name"
      />
      <FieldLabel
        class="battle-team-radio-group__pick has-data-[state=checked]:border-transparent has-data-[state=checked]:bg-transparent dark:has-data-[state=checked]:bg-transparent"
        :for="`${idPrefix}-${index}`"
        :data-test="`${testIdBlock}__pokemon-${index + 1}-radio`"
      >
        <RadioGroupItem
          :id="`${idPrefix}-${index}`"
          class="battle-team-radio-group__radio"
          :value="String(index)"
          :disabled="disabled || member.isFainted"
        />
        <TeamRailMemberBody
          :class="{
            'battle-team-radio-group__body--fainted': member.isFainted,
          }"
          :name="member.name"
          :gender="member.gender"
          :level="member.level"
          :ailment="member.isFainted ? null : member.ailment"
          :has-fainted-tag="member.isFainted"
          :current-hp="member.currentHp"
          :max-hp="member.maxHp"
        />
      </FieldLabel>
    </div>
  </RadioGroup>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, useId } from 'vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { FieldLabel } from '@/shared/components/ui/field';
import { RadioGroup, RadioGroupItem } from '@/shared/components/ui/radio-group';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import PokemonDetailsButton from './PokemonDetailsButton.vue';
import TeamRailMemberBody from './TeamRailMemberBody.vue';

withDefaults(
  defineProps<{
    testIdBlock: string;
    disabled?: boolean;
    isGridBelowWideLayout?: boolean;
    hasDetailsButtons?: boolean;
  }>(),
  {
    disabled: false,
    isGridBelowWideLayout: false,
    hasDetailsButtons: false,
  },
);

const combatantIndex = defineModel<string | null>({ required: true });

const page = usePage<BattleShowPageProps>();

const idPrefix = useId();

const bench = computed(() =>
  page.props.player.team
    .map((member, index) => ({ member, index }))
    .filter(({ member }) => !member.isActive),
);
</script>

<style scoped>
@reference '@css/app.css';

.battle-team-radio-group {
  @apply flex flex-col gap-1.5;
}

.battle-team-radio-group__member {
  @apply flex min-h-16 w-full items-center gap-2 rounded-lg border border-border bg-card py-2 pr-2.5 pl-2 transition-colors duration-250 has-[[role=radio]:focus-visible]:ring-[3px] has-[[role=radio]:focus-visible]:ring-ring/50 motion-reduce:transition-none;
}

.battle-team-radio-group__member:not(
    :has([data-state='checked'], [data-disabled])
  ):hover {
  @apply bg-muted/50;
}

.battle-team-radio-group__member:has([data-state='checked']) {
  @apply border-muted-foreground/40 bg-muted;
}

.battle-team-radio-group__member:has([data-state='checked'])
  :deep(
    :is(
      .team-rail-member-body__level,
      .team-rail-member-body__hp-label,
      .team-rail-member-body__hp-value
    )
  ) {
  @apply text-foreground/75;
}

.battle-team-radio-group__pick {
  @apply flex min-w-0 flex-1 cursor-pointer items-center self-stretch font-normal has-data-disabled:cursor-default;
}

.battle-team-radio-group__body--fainted {
  @apply opacity-50;
}

.battle-team-radio-group__portrait {
  @apply m-0.5 size-9 shrink-0;
}

.battle-team-radio-group__member--fainted .battle-team-radio-group__portrait {
  @apply opacity-50 grayscale;
}

.battle-team-radio-group__pick :deep(.battle-team-radio-group__radio) {
  @apply sr-only;
}

.battle-team-radio-group--grid {
  @apply grid grid-cols-1 @md:grid-cols-2 @2xl:grid-cols-3 @5xl:flex;
}
</style>
