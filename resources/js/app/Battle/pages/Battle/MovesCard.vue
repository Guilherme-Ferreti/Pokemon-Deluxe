<template>
  <Card data-test="moves-card">
    <CardHeader>
      <CardTitle>Choose a move</CardTitle>
    </CardHeader>
    <CardContent class="flex flex-col gap-4">
      <RadioGroup
        v-model="form.moveSlot"
        class="grid grid-cols-1 gap-2 @md:grid-cols-2"
        aria-label="Choose a move"
        :disabled="form.processing"
      >
        <FieldLabel
          v-for="(move, index) in page.props.moves"
          :key="move.id"
          class="flex min-h-16 w-full cursor-pointer items-start gap-3 rounded-lg border border-border bg-card px-3.5 py-3 font-normal transition-colors duration-200 not-has-data-[state=checked]:not-has-data-disabled:hover:bg-muted/50 has-focus-visible:ring-[3px] has-focus-visible:ring-ring/50 has-data-disabled:cursor-default has-data-[state=checked]:border-primary has-data-[state=checked]:bg-muted motion-reduce:transition-none"
          :for="`${idPrefix}-${index}`"
        >
          <span class="flex min-w-0 flex-1 flex-col items-start gap-2">
            <MoveNameLabel
              class="text-sm font-semibold"
              :name="move.name"
              :is-z-move="move.isZMove"
              :is-dynamax-move="move.isDynamaxMove"
            />
            <PokemonTypeBadges :types="[move.type]" />
          </span>
          <RadioGroupItem
            :id="`${idPrefix}-${index}`"
            class="[&_[data-slot=radio-group-indicator]_svg]:animate-in [&_[data-slot=radio-group-indicator]_svg]:duration-200 [&_[data-slot=radio-group-indicator]_svg]:zoom-in-50 [&_[data-slot=radio-group-indicator]_svg]:fade-in motion-reduce:[&_[data-slot=radio-group-indicator]_svg]:animate-none"
            :data-test="`moves-card__move-${index + 1}-radio`"
            :value="String(index + 1)"
          />
        </FieldLabel>
      </RadioGroup>
      <Button
        type="button"
        class="self-end"
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
