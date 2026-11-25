<template>
  <RadioGroup
    v-model="combatantIndex"
    aria-label="Bench"
    :class="
      isGridBelowWideLayout
        ? 'grid grid-cols-1 gap-1.5 @md:grid-cols-2 @2xl:grid-cols-3 @5xl:flex @5xl:flex-col'
        : 'flex flex-col gap-1.5'
    "
    :disabled="disabled"
  >
    <div
      v-for="{ member, index } in bench"
      :key="index"
      class="flex min-h-16 w-full items-center gap-2 rounded-lg border border-border bg-card py-2 pr-2.5 pl-2 transition-colors duration-250 not-has-data-[state=checked]:not-has-data-disabled:hover:bg-muted/50 has-data-[state=checked]:border-muted-foreground/40 has-data-[state=checked]:bg-muted has-[[role=radio]:focus-visible]:ring-[3px] has-[[role=radio]:focus-visible]:ring-ring/50 motion-reduce:transition-none"
    >
      <PokemonDetailsButton
        v-if="hasDetailsButtons"
        :pokemon="member"
        :is-fainted="member.isFainted"
        :test-id="`${testIdBlock}__pokemon-${index + 1}-details-button`"
      />
      <SpeciesPortrait
        v-else
        class="m-0.5 size-9 shrink-0"
        :class="{ 'opacity-50 grayscale': member.isFainted }"
        :url="member.portrait"
        :name="member.name"
      />
      <FieldLabel
        class="flex min-w-0 flex-1 cursor-pointer items-center self-stretch font-normal has-data-disabled:cursor-default has-data-[state=checked]:border-transparent has-data-[state=checked]:bg-transparent dark:has-data-[state=checked]:bg-transparent"
        :for="`${idPrefix}-${index}`"
        :data-test="`${testIdBlock}__pokemon-${index + 1}-radio`"
      >
        <RadioGroupItem
          :id="`${idPrefix}-${index}`"
          class="sr-only"
          :value="String(index)"
          :disabled="disabled || member.isFainted"
        />
        <TeamRailMemberBody
          :class="{ 'opacity-50': member.isFainted }"
          :is-highlighted="combatantIndex === String(index)"
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
