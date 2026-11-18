<template>
  <Card
    class="moves-card"
    data-test="moves-card"
  >
    <CardHeader>
      <CardTitle>Choose a move</CardTitle>
    </CardHeader>
    <CardContent class="moves-card__content">
      <RadioGroup
        v-model="form.moveSlot"
        class="moves-card__grid"
        aria-label="Choose a move"
        :disabled="form.processing"
      >
        <FieldLabel
          v-for="(move, index) in page.props.moves"
          :key="move.id"
          class="moves-card__move"
          :for="`${idPrefix}-${index}`"
        >
          <span class="moves-card__move-body">
            <MoveNameLabel
              class="moves-card__move-name"
              :name="move.name"
              :is-z-move="move.isZMove"
              :is-dynamax-move="move.isDynamaxMove"
            />
            <PokemonTypeBadges :types="[move.type]" />
          </span>
          <RadioGroupItem
            :id="`${idPrefix}-${index}`"
            class="moves-card__radio"
            :data-test="`moves-card__move-${index + 1}-radio`"
            :value="String(index + 1)"
          />
        </FieldLabel>
      </RadioGroup>
      <Button
        type="button"
        class="moves-card__attack-button"
        data-test="moves-card__attack-button"
        :disabled="form.processing"
        @click="attack"
      >
        Attack
      </Button>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { useId } from 'vue';
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import { FieldLabel } from '@/shared/components/ui/field';
import { RadioGroup, RadioGroupItem } from '@/shared/components/ui/radio-group';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

const idPrefix = useId();

const form = useForm({
  moveSlot: '1',
});

function attack(): void {
  form
    .transform((data) => ({ moveSlot: Number(data.moveSlot) }))
    .post(route('battle.web.turn'), {
      preserveScroll: true,
      preserveState: true,
    });
}
</script>

<style scoped>
@reference '@css/app.css';

.moves-card__content {
  @apply flex flex-col gap-4;
}

.moves-card__grid {
  @apply grid grid-cols-1 gap-2 @md:grid-cols-2;
}

.moves-card__move {
  @apply flex min-h-16 w-full cursor-pointer items-start gap-3 rounded-lg border border-border bg-card px-3.5 py-3 font-normal transition-colors duration-200 has-focus-visible:ring-[3px] has-focus-visible:ring-ring/50 has-data-disabled:cursor-default motion-reduce:transition-none;
}

.moves-card__move:has([data-state='checked']) {
  @apply border-primary bg-muted;
}

.moves-card__move-body {
  @apply flex min-w-0 flex-1 flex-col items-start gap-2;
}

.moves-card__move-name {
  @apply text-sm font-semibold;
}

.moves-card__radio :deep([data-slot='radio-group-indicator'] svg) {
  @apply animate-in duration-200 zoom-in-50 fade-in motion-reduce:animate-none;
}

.moves-card__attack-button {
  @apply self-end;
}
</style>
