<template>
  <RadioGroup
    v-model="combatantIndex"
    class="battle-team-radio-group"
  >
    <Field
      v-for="(member, index) in page.props.player.team"
      :key="index"
      orientation="horizontal"
      :class="[
        'battle-team-radio-group__member',
        { 'battle-team-radio-group__member--disabled': isUnavailable(member) },
      ]"
      :data-test="`${testIdBlock}__pokemon-${index + 1}-radio`"
    >
      <RadioGroupItem
        :id="`${idPrefix}-${index}`"
        :value="String(index)"
        :disabled="isUnavailable(member)"
      />
      <FieldLabel
        class="battle-team-radio-group__label"
        :for="`${idPrefix}-${index}`"
      >
        <SpeciesPortrait
          :url="member.portrait"
          :name="member.name"
        />
        <span class="battle-team-radio-group__details">
          <span class="battle-team-radio-group__name">
            {{ member.name }}
            <span class="battle-team-radio-group__level">
              Lv {{ member.level }}
            </span>
            <AilmentBadge
              v-if="member.ailment && !member.isFainted"
              :ailment="member.ailment"
            />
            <Badge
              v-if="member.isActive && !member.isFainted"
              variant="secondary"
            >
              In battle
            </Badge>
          </span>
          <span class="battle-team-radio-group__hp">
            <HpProgress
              :percentage="Math.ceil((member.currentHp / member.maxHp) * 100)"
              :label="`${member.name} HP`"
            />
            <span class="battle-team-radio-group__hp-value">
              {{
                member.isFainted
                  ? 'Fainted'
                  : `${member.currentHp} / ${member.maxHp}`
              }}
            </span>
          </span>
        </span>
      </FieldLabel>
    </Field>
  </RadioGroup>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useId } from 'vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Field, FieldLabel } from '@/shared/components/ui/field';
import { RadioGroup, RadioGroupItem } from '@/shared/components/ui/radio-group';
import type {
  BattleShowPageProps,
  BattleTeamMember,
} from '../../types/page-props/BattleShowPageProps';
import AilmentBadge from './AilmentBadge.vue';
import HpProgress from './HpProgress.vue';

defineProps<{
  testIdBlock: string;
}>();

const combatantIndex = defineModel<string | null>({ required: true });

const page = usePage<BattleShowPageProps>();

const idPrefix = useId();

function isUnavailable(member: BattleTeamMember): boolean {
  return member.isFainted || member.isActive;
}
</script>

<style scoped>
@reference '@css/app.css';

.battle-team-radio-group {
  @apply gap-2;
}

.battle-team-radio-group__member {
  @apply cursor-pointer rounded-md border p-3 has-data-[state=checked]:border-primary;
}

.battle-team-radio-group__member--disabled {
  @apply cursor-not-allowed opacity-50;
}

.battle-team-radio-group__label {
  @apply flex min-w-0 flex-1 cursor-pointer items-center gap-3 font-normal;
}

.battle-team-radio-group__details {
  @apply flex min-w-0 flex-1 flex-col gap-1;
}

.battle-team-radio-group__name {
  @apply flex flex-wrap items-center gap-1 font-semibold;
}

.battle-team-radio-group__level {
  @apply font-normal text-muted-foreground;
}

.battle-team-radio-group__hp {
  @apply flex items-center gap-2;
}

.battle-team-radio-group__hp-value {
  @apply min-w-16 text-right text-xs text-muted-foreground tabular-nums;
}
</style>
