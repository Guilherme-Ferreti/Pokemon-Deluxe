<template>
  <Dialog :open="page.props.battle.isAwaitingSendOut">
    <DialogContent
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
      <BattleTeamRadioGroup
        v-model="form.combatantIndex"
        test-id-block="send-out-dialog"
      />
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
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/shared/components/ui/dialog';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BattleTeamRadioGroup from './BattleTeamRadioGroup.vue';

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

.send-out-dialog__send-out-button {
  @apply w-full;
}
</style>
