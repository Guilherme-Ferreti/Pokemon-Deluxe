<template>
  <Sheet :open="open">
    <SheetContent
      side="bottom"
      class="@container max-h-[85vh] gap-2.5 overflow-y-auto rounded-t-2xl px-4 pt-2.5 pb-[max(1.25rem,env(safe-area-inset-bottom))]"
      data-test="send-out-sheet"
      :show-close-button="false"
      @escape-key-down.prevent
      @pointer-down-outside.prevent
      @interact-outside.prevent
    >
      <span
        class="h-1 w-10 shrink-0 self-center rounded-full bg-muted-foreground/40"
        aria-hidden="true"
      />
      <SheetHeader class="p-0">
        <SheetTitle>{{ page.props.player.name }} fainted!</SheetTitle>
        <SheetDescription>
          Pick who to send out, then press Send out. This doesn't use a turn.
        </SheetDescription>
      </SheetHeader>
      <BattleTeamRadioGroup
        v-model="form.combatantIndex"
        test-id-block="send-out-sheet"
        is-grid-below-wide-layout
        :disabled="form.processing"
      />
      <Button
        type="button"
        size="lg"
        class="self-end"
        data-test="send-out-sheet__send-out-button"
        :disabled="form.combatantIndex === null || form.processing"
        @click="sendOut"
      >
        Send out
      </Button>
    </SheetContent>
  </Sheet>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/shared/components/ui/button';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from '@/shared/components/ui/sheet';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BattleTeamRadioGroup from './BattleTeamRadioGroup.vue';

defineProps<{
  open: boolean;
}>();

const page = usePage<BattleShowPageProps>();

const form = useForm<{ combatantIndex: string | null }>({
  combatantIndex: null,
});

function sendOut(): void {
  form
    .transform((data) => ({ combatantIndex: Number(data.combatantIndex) }))
    .post(route('battle.web.send-out'), {
      onSuccess: () => form.reset(),
    });
}
</script>
