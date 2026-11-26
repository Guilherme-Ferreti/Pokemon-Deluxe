<template>
  <div
    class="flex flex-col gap-4"
    data-test="ball-picker-list"
  >
    <RadioGroup
      v-if="page.props.pokeballs.length > 0"
      v-model="form.pokeball"
      class="-m-1 max-h-80 scrollbar-thin gap-2 overflow-y-auto overscroll-contain p-1"
      aria-label="Choose a ball"
      :disabled="form.processing"
    >
      <AppChoiceCard
        v-for="pokeball in page.props.pokeballs"
        :key="pokeball.slug"
        :data-test="`ball-picker-list__ball-${pokeball.slug}-radio`"
        :value="pokeball.slug"
      >
        <ItemSprite
          class="shrink-0"
          :name="pokeball.name"
          :url="pokeball.sprite"
        />
        <span
          class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5 text-sm font-medium"
        >
          {{ pokeball.name }}
          <Badge
            v-if="pokeball.isBoosted"
            variant="outline"
            class="border-green-600/40 bg-green-50 text-green-700 dark:border-green-400/40 dark:bg-green-950 dark:text-green-400"
          >
            Boosted
          </Badge>
        </span>
        <strong class="text-sm tabular-nums">x{{ pokeball.multiplier }}</strong>
        <span
          class="w-12 text-right text-xs text-muted-foreground tabular-nums"
        >
          x{{ pokeball.quantity.toLocaleString('en-US') }}
        </span>
      </AppChoiceCard>
    </RadioGroup>
    <p
      v-else
      class="rounded-lg bg-muted p-3 text-sm text-muted-foreground"
      data-test="ball-picker-list__empty-state"
    >
      You have no balls left.
    </p>
    <Button
      v-if="page.props.pokeballs.length > 0"
      type="button"
      class="self-end"
      data-test="ball-picker-list__throw-button"
      :disabled="form.processing || !form.pokeball"
      @click="throwBall"
    >
      Throw ball
    </Button>
  </div>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import AppChoiceCard from '@/shared/components/form/AppChoiceCard.vue';
import ItemSprite from '@/shared/components/game/ItemSprite.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { RadioGroup } from '@/shared/components/ui/radio-group';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const emit = defineEmits<{
  thrown: [];
}>();

const page = usePage<BattleShowPageProps>();

const form = useForm({
  pokeball: page.props.pokeballs[0]?.slug ?? '',
});

function throwBall(): void {
  form.post(route('battle.web.throw-ball'), {
    preserveScroll: true,
    onSuccess: () => emit('thrown'),
  });
}
</script>
