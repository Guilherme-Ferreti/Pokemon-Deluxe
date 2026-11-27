<template>
  <Tooltip v-if="isAtMaxBoost && !isOpen">
    <TooltipTrigger as-child>
      <span tabindex="-1">
        <Button
          type="button"
          variant="outline"
          data-test="pokemon-boost-dialog__open-button"
          disabled
        >
          <IconArrowUp />
          Boost
        </Button>
      </span>
    </TooltipTrigger>
    <TooltipContent>This Pokémon is at maximum boost level</TooltipContent>
  </Tooltip>
  <Dialog
    v-else
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
    <DialogContent data-test="pokemon-boost-dialog">
      <PokemonBoostResult
        v-if="boostResult"
        :result="boostResult"
        :can-use-more="canUseStones"
        @use-more="resetDialog"
      />
      <div
        v-else
        class="flex flex-col gap-4"
      >
        <DialogHeader>
          <DialogTitle>Use boost stones</DialogTitle>
          <DialogDescription>
            Each stone that works adds +1 boost. Failed stones are used up.
          </DialogDescription>
        </DialogHeader>
        <div class="flex items-center justify-center gap-6">
          <span class="pokemon-boost-dialog__stat">
            <span class="pokemon-boost-dialog__stat-title">Boost now</span>
            <span
              class="pokemon-boost-dialog__stat-indicator"
              data-test="pokemon-boost-dialog__boost-indicator"
            >
              +{{ pokemon.boost }}
            </span>
          </span>
          <span class="pokemon-boost-dialog__stat">
            <span class="pokemon-boost-dialog__stat-title">You own</span>
            <span
              class="pokemon-boost-dialog__stat-indicator"
              data-test="pokemon-boost-dialog__owned-indicator"
            >
              {{ boostStones.quantity.toLocaleString('en-US') }}
            </span>
          </span>
          <span
            v-if="boostStones.failureChance !== null"
            class="pokemon-boost-dialog__stat"
          >
            <span class="pokemon-boost-dialog__stat-title">Failure chance</span>
            <span
              class="pokemon-boost-dialog__stat-indicator"
              data-test="pokemon-boost-dialog__failure-indicator"
            >
              {{ formatPercentage(boostStones.failureChance) }}
            </span>
          </span>
        </div>
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
            <AppChoiceCard
              data-test="pokemon-boost-dialog__count-mode-radio"
              value="count"
            >
              <span class="flex flex-wrap items-center gap-2">
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
              </span>
            </AppChoiceCard>
            <AppChoiceCard
              data-test="pokemon-boost-dialog__target-mode-radio"
              value="target"
            >
              <span class="flex flex-wrap items-center gap-2">
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
              </span>
            </AppChoiceCard>
          </RadioGroup>
          <FieldError v-if="form.errors.maxStones || form.errors.targetBoost">
            {{ form.errors.maxStones ?? form.errors.targetBoost }}
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
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import type { FlashData } from '@inertiajs/core';
import { useForm, usePage } from '@inertiajs/vue3';
import { IconArrowUp } from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import AppChoiceCard from '@/shared/components/form/AppChoiceCard.vue';
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
  FieldError,
  FieldLegend,
  FieldSet,
} from '@/shared/components/ui/field';
import { Input } from '@/shared/components/ui/input';
import { RadioGroup } from '@/shared/components/ui/radio-group';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import { route } from '@/shared/types/helpers/route';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';
import PokemonBoostResult from './PokemonBoostResult.vue';

const DEFAULT_STONE_COUNT = 20;

const DEFAULT_BOOST_STEP = 10;

const page = usePage<BoxShowPageProps>();

const pokemon = computed(() => page.props.pokemon);

const boostStones = computed(() => page.props.boostStones);

const maxBoost = computed(() => page.props.boostStones.maxBoost);

const isOpen = ref(false);

const mode = ref<'count' | 'target'>('count');

const stoneCount = ref(1);

const targetBoost = ref(1);

const boostResult = ref<NonNullable<FlashData['boostResult']> | null>(null);

const form = useForm({
  maxStones: 1,
  targetBoost: null as number | null,
});

const isAtMaxBoost = computed(() => pokemon.value.boost >= maxBoost.value);

const canUseStones = computed(
  () =>
    boostStones.value.quantity > 0 && boostStones.value.failureChance !== null,
);

const formatPercentage = (percentage: number) =>
  `${percentage.toLocaleString('en-US', { maximumFractionDigits: 2 })}%`;

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
        ? { maxStones: stoneCount.value, targetBoost: null }
        : {
            maxStones: boostStones.value.quantity,
            targetBoost: targetBoost.value,
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

<style scoped>
@reference '@css/app.css';

.pokemon-boost-dialog__stat {
  @apply flex flex-col;
}

.pokemon-boost-dialog__stat-title {
  @apply text-xs text-muted-foreground;
}

.pokemon-boost-dialog__stat-indicator {
  @apply text-center text-2xl font-bold tabular-nums;
}
</style>
