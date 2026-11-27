<template>
  <Card data-test="battle-team-rail">
    <CardHeader>
      <CardTitle>Your team</CardTitle>
    </CardHeader>
    <CardContent class="flex flex-col gap-3">
      <TeamRailGroup
        v-if="pokemonInBattle"
        heading="In battle"
        data-test="battle-team-rail__in-battle-list"
      >
        <li
          class="flex items-center gap-2 rounded-lg border border-border bg-card py-2 pr-2.5 pl-2"
          :data-test="`battle-team-rail__pokemon-${pokemonInBattle.index + 1}-item`"
        >
          <PokemonDetailsButton
            :pokemon="pokemonInBattle.member"
            :is-fainted="pokemonInBattle.member.isFainted"
            :test-id="`battle-team-rail__pokemon-${pokemonInBattle.index + 1}-details-button`"
          />
          <TeamRailMemberBody
            :class="{ 'opacity-50': pokemonInBattle.member.isFainted }"
            :name="pokemonInBattle.member.name"
            :gender="pokemonInBattle.member.gender"
            :ailment="
              pokemonInBattle.member.isFainted
                ? null
                : pokemonInBattle.member.ailment
            "
            :has-fainted-tag="pokemonInBattle.member.isFainted"
          />
        </li>
      </TeamRailGroup>
      <TeamRailGroup
        v-if="hasBench"
        heading="Bench"
        data-test="battle-team-rail__bench-list"
        :is-list="false"
      >
        <BattleTeamRadioGroup
          v-model="form.combatantIndex"
          test-id-block="battle-team-rail"
          is-grid-below-wide-layout
          has-details-buttons
          :disabled="!isOngoing || isRequestRunning"
        />
      </TeamRailGroup>
      <Button
        v-if="isOngoing && isAwaitingSendOut"
        type="button"
        size="lg"
        class="battle-team-rail__action-button"
        data-test="battle-team-rail__send-out-button"
        :disabled="!canAct"
        @click="sendOut"
      >
        Send out
      </Button>
      <Button
        v-else-if="isOngoing"
        type="button"
        variant="outline"
        size="lg"
        class="battle-team-rail__action-button"
        data-test="battle-team-rail__switch-button"
        :disabled="!canAct"
        @click="switchPokemon"
      >
        Switch
      </Button>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { Button } from '@/shared/components/ui/button';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import { useIsRequestRunning } from '@/shared/composables/useIsRequestRunning';
import { BattleStatus } from '@/shared/types/enums';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BattleTeamRadioGroup from './BattleTeamRadioGroup.vue';
import PokemonDetailsButton from './PokemonDetailsButton.vue';
import TeamRailGroup from './TeamRailGroup.vue';
import TeamRailMemberBody from './TeamRailMemberBody.vue';

const page = usePage<BattleShowPageProps>();

const isOngoing = computed(
  () => page.props.battle.status === BattleStatus.ONGOING,
);

const isAwaitingSendOut = computed(() => page.props.battle.isAwaitingSendOut);

const activeIndex = computed(() =>
  page.props.player.team.findIndex((member) => member.isActive),
);

const pokemonInBattle = computed(() =>
  activeIndex.value === -1
    ? null
    : {
        member: page.props.player.team[activeIndex.value],
        index: activeIndex.value,
      },
);

const hasBench = computed(() => page.props.player.team.length > 1);

const form = useForm<{ combatantIndex: string | null }>({
  combatantIndex: null,
});

watch([activeIndex, isAwaitingSendOut, isOngoing], () => {
  form.combatantIndex = null;
});

const isRequestRunning = useIsRequestRunning();

const canAct = computed(
  () => !isRequestRunning.value && form.combatantIndex !== null,
);

function sendOut(): void {
  form
    .transform((data) => ({ combatantIndex: Number(data.combatantIndex) }))
    .post(route('battle.web.send-out'));
}

function switchPokemon(): void {
  form
    .transform((data) => ({ combatantIndex: Number(data.combatantIndex) }))
    .post(route('battle.web.switch'), {
      onError: () => {
        form.combatantIndex = null;
      },
    });
}
</script>

<style scoped>
@reference '@css/app.css';

.battle-team-rail__action-button {
  @apply self-end;
}
</style>
