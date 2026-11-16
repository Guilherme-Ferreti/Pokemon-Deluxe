<template>
  <Dialog :open="page.props.sendOut !== null">
    <DialogContent
      v-if="page.props.sendOut"
      data-test="send-out-dialog"
      :show-close-button="false"
      @escape-key-down.prevent
      @pointer-down-outside.prevent
      @interact-outside.prevent
    >
      <DialogHeader>
        <DialogTitle>{{ page.props.player.name }} fainted!</DialogTitle>
        <DialogDescription>Choose who to send out next.</DialogDescription>
      </DialogHeader>
      <RadioGroup
        v-model="form.combatantIndex"
        class="send-out-dialog__team"
      >
        <Field
          v-for="(member, index) in page.props.sendOut.team"
          :key="index"
          orientation="horizontal"
          :class="[
            'send-out-dialog__member',
            { 'send-out-dialog__member--fainted': member.isFainted },
          ]"
          :data-test="`send-out-dialog__pokemon-${index + 1}-radio`"
        >
          <RadioGroupItem
            :id="`send-out-pokemon-${index}`"
            :value="String(index)"
            :disabled="member.isFainted"
          />
          <FieldLabel
            class="send-out-dialog__member-label"
            :for="`send-out-pokemon-${index}`"
          >
            <SpeciesPortrait
              :url="member.portrait"
              :name="member.name"
            />
            <span class="send-out-dialog__member-details">
              <span class="send-out-dialog__member-name">
                {{ member.name }}
                <span class="send-out-dialog__member-level">
                  Lv {{ member.level }}
                </span>
              </span>
              <span class="send-out-dialog__member-hp">
                <HpProgress
                  :percentage="
                    Math.ceil((member.currentHp / member.maxHp) * 100)
                  "
                  :label="`${member.name} HP`"
                />
                <span class="send-out-dialog__member-hp-value">
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
      <DialogFooter>
        <Button
          type="button"
          class="send-out-dialog__send-out-button"
          data-test="send-out-dialog__send-out-button"
          :disabled="form.combatantIndex === null || form.processing"
          @click="sendOut"
        >
          Send out
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/shared/components/ui/dialog';
import { Field, FieldLabel } from '@/shared/components/ui/field';
import { RadioGroup, RadioGroupItem } from '@/shared/components/ui/radio-group';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import HpProgress from './HpProgress.vue';

const page = usePage<BattleShowPageProps>();

const form = useForm<{ combatantIndex: string | null }>({
  combatantIndex: null,
});

function sendOut(): void {
  form
    .transform((data) => ({ combatantIndex: Number(data.combatantIndex) }))
    .post(route('battle.web.send-out'), {
      preserveScroll: true,
      onSuccess: () => form.reset(),
    });
}
</script>

<style scoped>
@reference '@css/app.css';

.send-out-dialog__team {
  @apply gap-2;
}

.send-out-dialog__member {
  @apply cursor-pointer rounded-md border p-3 has-data-[state=checked]:border-primary;
}

.send-out-dialog__member--fainted {
  @apply cursor-not-allowed opacity-50;
}

.send-out-dialog__member-label {
  @apply flex min-w-0 flex-1 cursor-pointer items-center gap-3 font-normal;
}

.send-out-dialog__member-details {
  @apply flex min-w-0 flex-1 flex-col gap-1;
}

.send-out-dialog__member-name {
  @apply font-semibold;
}

.send-out-dialog__member-level {
  @apply font-normal text-muted-foreground;
}

.send-out-dialog__member-hp {
  @apply flex items-center gap-2;
}

.send-out-dialog__member-hp-value {
  @apply min-w-16 text-right text-xs text-muted-foreground tabular-nums;
}

.send-out-dialog__send-out-button {
  @apply w-full;
}
</style>
