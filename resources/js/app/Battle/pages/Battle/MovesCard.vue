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
        <AppChoiceCard
          v-for="(move, index) in page.props.moves"
          :key="move.id"
          :data-test="`moves-card__move-${index + 1}-radio`"
          :value="String(index + 1)"
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
        </AppChoiceCard>
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
import AppChoiceCard from '@/shared/components/form/AppChoiceCard.vue';
import MoveNameLabel from '@/shared/components/game/MoveNameLabel.vue';
import PokemonTypeBadges from '@/shared/components/game/PokemonTypeBadges.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card';
import { RadioGroup } from '@/shared/components/ui/radio-group';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

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
