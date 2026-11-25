<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button
        type="button"
        variant="outline"
        data-test="battle-throw-ball-button"
        :disabled="page.props.battle.isAwaitingSendOut"
      >
        <IconPokeball class="size-5" />
        Throw ball
      </Button>
    </DialogTrigger>
    <DialogScrollContent
      class="sm:max-w-md"
      data-test="battle-throw-ball-button__dialog"
    >
      <DialogHeader>
        <DialogTitle>Throw a ball</DialogTitle>
        <DialogDescription>
          A throw uses your turn, so the wild
          {{ page.props.opponent.name }} still attacks if it breaks free.
        </DialogDescription>
      </DialogHeader>
      <BallPickerList @thrown="isOpen = false" />
    </DialogScrollContent>
  </Dialog>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconPokeball } from '@tabler/icons-vue';
import { ref, watch } from 'vue';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogDescription,
  DialogHeader,
  DialogScrollContent,
  DialogTitle,
  DialogTrigger,
} from '@/shared/components/ui/dialog';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';
import BallPickerList from './BallPickerList.vue';

const page = usePage<BattleShowPageProps>();

const isOpen = ref(false);

watch(
  () => page.props.battle.isAwaitingSendOut,
  (isAwaitingSendOut) => {
    if (isAwaitingSendOut) {
      isOpen.value = false;
    }
  },
);
</script>
