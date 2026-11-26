<template>
  <div
    class="flex flex-col items-center gap-4 text-center"
    data-test="pokemon-boost-result"
  >
    <DialogHeader class="items-center text-center sm:text-center">
      <DialogTitle class="tabular-nums">Boost Results</DialogTitle>
      <DialogDescription class="sr-only">
        The result of using boost stones.
      </DialogDescription>
    </DialogHeader>
    <span
      class="flex items-center justify-center gap-6 text-xl font-bold tabular-nums"
      data-test="pokemon-boost-result__new-boost-indicator"
    >
      <span>+{{ result.boostBefore }}</span>
      <IconArrowNarrowRight />
      <span>+{{ result.boostAfter }}</span>
    </span>
    <p
      class="text-sm text-muted-foreground"
      data-test="pokemon-boost-result__summary-text"
    >
      Used {{ pluralizeStones(result.stonesUsed) }}:
      {{ result.successes }} worked, {{ result.failures }} failed.
      {{ pluralizeStones(page.props.boostStones.quantity) }} left.
    </p>
    <DialogFooter class="w-full sm:justify-center">
      <Button
        v-if="canUseMore"
        type="button"
        variant="outline"
        data-test="pokemon-boost-result__use-more-button"
        @click="emit('use-more')"
      >
        Use more
      </Button>
      <DialogClose as-child>
        <Button
          type="button"
          data-test="pokemon-boost-result__done-button"
        >
          Done
        </Button>
      </DialogClose>
    </DialogFooter>
  </div>
</template>

<script setup lang="ts">
import type { FlashData } from '@inertiajs/core';
import { usePage } from '@inertiajs/vue3';
import { IconArrowNarrowRight } from '@tabler/icons-vue';
import { Button } from '@/shared/components/ui/button';
import {
  DialogClose,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/shared/components/ui/dialog';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

defineProps<{
  result: NonNullable<FlashData['boostResult']>;
  canUseMore: boolean;
}>();

const emit = defineEmits<{
  'use-more': [];
}>();

const page = usePage<BoxShowPageProps>();

const pluralizeStones = (count: number) =>
  `${count.toLocaleString('en-US')} ${count === 1 ? 'stone' : 'stones'}`;
</script>
