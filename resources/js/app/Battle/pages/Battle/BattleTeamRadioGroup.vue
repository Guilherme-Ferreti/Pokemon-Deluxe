<template>
  <RadioGroup
    v-model="combatantIndex"
    aria-label="Bench"
    :class="
      isGridBelowWideLayout
        ? 'grid grid-cols-1 gap-2 @md:grid-cols-2 @2xl:grid-cols-3 @5xl:flex @5xl:flex-col'
        : 'flex flex-col gap-2'
    "
    :disabled="disabled"
  >
    <AppChoiceCard
      v-for="{ member, index } in bench"
      :key="index"
      :data-test="`${testIdBlock}__pokemon-${index + 1}-radio`"
      :value="String(index)"
      :disabled="disabled || member.isFainted"
    >
      <template
        v-if="hasDetailsButtons"
        #leading
      >
        <PokemonDetailsButton
          :pokemon="member"
          :is-fainted="member.isFainted"
          :test-id="`${testIdBlock}__pokemon-${index + 1}-details-button`"
        />
      </template>
      <SpeciesPortrait
        v-if="!hasDetailsButtons"
        class="m-0.5 size-9 shrink-0"
        :class="{ grayscale: member.isFainted }"
        :url="member.portrait"
        :name="member.name"
      />
      <TeamRailMemberBody
        :name="member.name"
        :gender="member.gender"
        :level="member.level"
        :ailment="member.isFainted ? null : member.ailment"
        :has-fainted-tag="member.isFainted"
        :current-hp="member.currentHp"
        :max-hp="member.maxHp"
      />
    </AppChoiceCard>
  </RadioGroup>
</template>
<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppChoiceCard from '@/shared/components/form/AppChoiceCard.vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { RadioGroup } from '@/shared/components/ui/radio-group';
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

const bench = computed(() =>
  page.props.player.team
    .map((member, index) => ({ member, index }))
    .filter(({ member }) => !member.isActive),
);
</script>
