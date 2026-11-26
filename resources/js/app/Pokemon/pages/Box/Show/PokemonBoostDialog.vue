<template>
  <Dialog
    v-model:open="isOpen"
    @update:open="(open) => open && resetDialog()"
  >
    <DialogTrigger as-child>
      <Button
        type="button"
        variant="outline"
        data-test="pokemon-boost-dialog__open-button"
      >
        <IconArrowUp />
        Boost
      </Button>
    </DialogTrigger>
    <DialogContent
      v-if="boostResult"
      class="items-center text-center"
      data-test="pokemon-boost-dialog__result"
    >
      <DialogHeader class="items-center text-center sm:text-center">
        <DialogTitle class="tabular-nums">
          Boost +{{ boostResult.boostBefore }} → +{{ boostResult.boostAfter }}
        </DialogTitle>
        <DialogDescription class="sr-only">
          The result of using boost stones.
        </DialogDescription>
      </DialogHeader>
      <span
        class="text-5xl font-bold tabular-nums"
        data-test="pokemon-boost-dialog__new-boost-indicator"
      >
        +{{ boostResult.boostAfter }}
      </span>
      <p
        class="text-sm text-muted-foreground"
        data-test="pokemon-boost-dialog__summary-text"
      >
        Used {{ pluralizeStones(boostResult.stonesUsed) }}:
        {{ boostResult.successes }} worked, {{ boostResult.failures }} failed.
        {{ pluralizeStones(boostStones.quantity) }} left.
      </p>
      <DialogFooter class="w-full sm:justify-center">
        <Button
          v-if="canUseStones"
          type="button"
          variant="outline"
          data-test="pokemon-boost-dialog__use-more-button"
          @click="resetDialog"
        >
          Use more
        </Button>
        <DialogClose as-child>
          <Button
            type="button"
            data-test="pokemon-boost-dialog__done-button"
          >
            Done
          </Button>
        </DialogClose>
      </DialogFooter>
    </DialogContent>
    <DialogContent
      v-else
      data-test="pokemon-boost-dialog"
    >
      <DialogHeader>
        <DialogTitle>Use boost stones</DialogTitle>
        <DialogDescription>
          Each stone that works adds +1 boost (+1% HP and damage). Failed stones
          are used up. Above +{{ safeUntil }}, stones can fail.
        </DialogDescription>
      </DialogHeader>
      <div class="flex gap-6">
        <span class="flex flex-col">
          <span class="text-xs text-muted-foreground">Boost now</span>
          <span
            class="text-2xl font-bold tabular-nums"
            data-test="pokemon-boost-dialog__boost-indicator"
          >
            +{{ pokemon.boost }}
          </span>
        </span>
        <span class="flex flex-col">
          <span class="text-xs text-muted-foreground">You own</span>
          <span
            class="text-2xl font-bold tabular-nums"
            data-test="pokemon-boost-dialog__owned-indicator"
          >
            {{ boostStones.quantity.toLocaleString('en-US') }}
          </span>
        </span>
      </div>
      <p
        v-if="boostStones.failureChance === null"
        class="text-sm"
      >
        {{ pokemon.name }} is at the max boost (+{{ maxBoost }}).
      </p>
      <p
        v-else
        class="text-sm"
        data-test="pokemon-boost-dialog__next-stone-text"
      >
        Next stone:
        <span class="tabular-nums">{{ `${nextBoostStep}, ` }}</span>
        <span
          class="font-medium"
          :class="failureChanceClass"
        >
          {{
            boostStones.failureChance === 0
              ? 'always works'
              : `${formatPercentage(boostStones.failureChance)} chance to fail`
          }}
        </span>
      </p>
      <p
        v-if="boostStones.quantity === 0"
        class="text-sm text-muted-foreground"
        data-test="pokemon-boost-dialog__no-stones-text"
      >
        You don't own any boost stones. Buy them in the shop.
      </p>
      <FieldSet v-if="canUseStones">
        <FieldLegend variant="label">How many</FieldLegend>
        <RadioGroup
          v-model="mode"
          class="gap-2"
        >
          <Field
            orientation="horizontal"
            class="rounded-md border p-3 has-data-[state=checked]:border-primary"
            data-test="pokemon-boost-dialog__count-mode-radio"
          >
            <RadioGroupItem
              id="boost-mode-count"
              value="count"
            />
            <FieldLabel
              class="flex flex-wrap items-center gap-2 font-normal"
              for="boost-mode-count"
            >
              Use up to
              <Input
                v-model.number="stoneCount"
                class="h-8 w-20 tabular-nums"
                type="number"
                min="1"
                aria-label="Stones"
                data-test="pokemon-boost-dialog__stone-count-input"
                :max="boostStones.quantity"
                @focus="mode = 'count'"
              />
              stones
            </FieldLabel>
          </Field>
          <Field
            orientation="horizontal"
            class="rounded-md border p-3 has-data-[state=checked]:border-primary"
            data-test="pokemon-boost-dialog__target-mode-radio"
          >
            <RadioGroupItem
              id="boost-mode-target"
              value="target"
            />
            <FieldLabel
              class="flex flex-wrap items-center gap-2 font-normal"
              for="boost-mode-target"
            >
              Use until +
              <Input
                v-model.number="targetBoost"
                class="h-8 w-20 tabular-nums"
                type="number"
                aria-label="Target boost"
                data-test="pokemon-boost-dialog__target-boost-input"
                :min="pokemon.boost + 1"
                :max="maxBoost"
                @focus="mode = 'target'"
              />
              boost
            </FieldLabel>
          </Field>
        </RadioGroup>
        <FieldError v-if="form.errors.max_stones || form.errors.target_boost">
          {{ form.errors.max_stones ?? form.errors.target_boost }}
        </FieldError>
      </FieldSet>
      <DialogFooter>
        <DialogClose as-child>
          <Button
            type="button"
            variant="outline"
          >
            Cancel
          </Button>
        </DialogClose>
        <Button
          type="button"
          data-test="pokemon-boost-dialog__submit-button"
          :disabled="!canUseStones || form.processing"
          @click="useStones"
        >
          Use stones
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import type { FlashData } from '@inertiajs/core';
import { useForm, usePage } from '@inertiajs/vue3';
import { IconArrowUp } from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/shared/components/ui/dialog';
import {
  Field,
  FieldError,
  FieldLabel,
  FieldLegend,
  FieldSet,
} from '@/shared/components/ui/field';
import { Input } from '@/shared/components/ui/input';
import { RadioGroup, RadioGroupItem } from '@/shared/components/ui/radio-group';
import { route } from '@/shared/types/helpers/route';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

const DEFAULT_STONE_COUNT = 20;

const DEFAULT_BOOST_STEP = 10;

const page = usePage<BoxShowPageProps>();

const pokemon = computed(() => page.props.pokemon);

const boostStones = computed(() => page.props.boostStones);

const maxBoost = computed(() => page.props.boostStones.maxBoost);

const safeUntil = computed(() => page.props.boostStones.safeUntil);

const isOpen = ref(false);

const mode = ref<'count' | 'target'>('count');

const stoneCount = ref(1);

const targetBoost = ref(1);

const boostResult = ref<NonNullable<FlashData['boostResult']> | null>(null);

const form = useForm({
  max_stones: 1,
  target_boost: null as number | null,
});

const canUseStones = computed(
  () =>
    boostStones.value.quantity > 0 && boostStones.value.failureChance !== null,
);

const nextBoostStep = computed(
  () => `+${pokemon.value.boost} → +${pokemon.value.boost + 1}`,
);

const failureChanceClass = computed(() => {
  const failureChance = boostStones.value.failureChance ?? 0;

  if (failureChance === 0) {
    return 'text-emerald-700 dark:text-emerald-400';
  }

  return failureChance < 40
    ? 'text-amber-700 dark:text-amber-400'
    : 'text-destructive';
});

const formatPercentage = (percentage: number) =>
  `${percentage.toLocaleString('en-US', { maximumFractionDigits: 2 })}%`;

const pluralizeStones = (count: number) =>
  `${count.toLocaleString('en-US')} ${count === 1 ? 'stone' : 'stones'}`;

const resetDialog = () => {
  boostResult.value = null;
  mode.value = 'count';
  stoneCount.value = Math.max(
    1,
    Math.min(DEFAULT_STONE_COUNT, boostStones.value.quantity),
  );
  targetBoost.value = Math.min(
    pokemon.value.boost + DEFAULT_BOOST_STEP,
    maxBoost.value,
  );
  form.clearErrors();
};

const useStones = () => {
  form
    .transform(() =>
      mode.value === 'count'
        ? { max_stones: stoneCount.value, target_boost: null }
        : {
            max_stones: boostStones.value.quantity,
            target_boost: targetBoost.value,
          },
    )
    .post(route('pokemon.web.boost', { pokemon: pokemon.value.id }), {
      preserveScroll: true,
      onFlash: (flash) => {
        if (flash.boostResult) {
          boostResult.value = flash.boostResult;
        }
      },
    });
};
</script>
