<template>
  <Card
    class="battle-team-rail"
    data-test="battle-team-rail"
  >
    <CardHeader class="battle-team-rail__header">
      <CardTitle>Your team</CardTitle>
      <Tooltip>
        <TooltipTrigger as-child>
          <Button
            type="button"
            variant="ghost"
            size="icon"
            class="battle-team-rail__info-button"
            :aria-label="`About Your team: ${note}`"
          >
            <IconInfoCircle aria-hidden="true" />
          </Button>
        </TooltipTrigger>
        <TooltipContent class="battle-team-rail__note">
          {{ note }}
        </TooltipContent>
      </Tooltip>
    </CardHeader>
    <CardContent class="battle-team-rail__content">
      <BattleTeamRadioGroup
        v-model="form.combatantIndex"
        test-id-block="battle-team-rail"
        is-grid-below-wide-layout
        :marks-pokemon-in-battle="isOngoing"
        :disabled="!isOngoing || isRequestRunning"
      />
      <Button
        v-if="isOngoing && isAwaitingSendOut"
        type="button"
        size="lg"
        class="battle-team-rail__action-button"
        data-test="battle-team-rail__send-out-button"
        :disabled="!canSendOut"
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
        :disabled="!canSwitch"
        @click="switchPokemon"
      >
        Switch
      </Button>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3';
import { IconInfoCircle } from '@tabler/icons-vue';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/shared/components/ui/button';
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
import { BattleStatus } from '@/shared/types/enums';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BattleTeamRadioGroup from './BattleTeamRadioGroup.vue';

const page = usePage<BattleShowPageProps>();

const isOngoing = computed(
  () => page.props.battle.status === BattleStatus.ONGOING,
);

const isAwaitingSendOut = computed(() => page.props.battle.isAwaitingSendOut);

const activeIndex = computed(() =>
  page.props.player.team.findIndex(
    (member) => member.isActive && !member.isFainted,
  ),
);

const pickableIndex = computed(() =>
  isOngoing.value && activeIndex.value !== -1
    ? String(activeIndex.value)
    : null,
);

const form = useForm<{ combatantIndex: string | null }>({
  combatantIndex: pickableIndex.value,
});

watch(pickableIndex, (index) => {
  form.combatantIndex = index;
});

const isRequestRunning = ref(false);

const stopListeningToStart = router.on('start', () => {
  isRequestRunning.value = true;
});

const stopListeningToFinish = router.on('finish', () => {
  isRequestRunning.value = false;
});

onBeforeUnmount(() => {
  stopListeningToStart();
  stopListeningToFinish();
});

const canSendOut = computed(
  () => !isRequestRunning.value && form.combatantIndex !== null,
);

const canSwitch = computed(
  () =>
    !isRequestRunning.value &&
    form.combatantIndex !== null &&
    form.combatantIndex !== pickableIndex.value,
);

const note = computed(() => {
  if (!isOngoing.value) {
    return 'HP as the battle ended.';
  }

  return isAwaitingSendOut.value
    ? 'Pick who to send out, then press Send out. This does not use a turn.'
    : "The highlighted Pokémon is the one in battle. To switch, pick another one and press Switch. Switching uses your turn. Fainted Pokémon can't be picked.";
});

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
        form.combatantIndex = pickableIndex.value;
      },
    });
}
</script>

<style scoped>
@reference '@css/app.css';

.battle-team-rail__header {
  @apply flex items-center gap-0.5;
}

.battle-team-rail__info-button {
  @apply -my-2 size-8 text-muted-foreground;
}

.battle-team-rail__note {
  @apply max-w-64;
}

.battle-team-rail__content {
  @apply flex flex-col gap-3;
}

.battle-team-rail__action-button {
  @apply self-end;
}
</style>
