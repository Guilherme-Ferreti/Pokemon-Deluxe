<template>
  <Sheet v-model:open="isOpen">
    <SheetContent
      side="bottom"
      class="pokemon-details-sheet"
      data-test="pokemon-details-sheet"
      :show-close-button="false"
    >
      <div
        ref="swipeArea"
        class="pokemon-details-sheet__swipe-area"
      >
        <span
          class="pokemon-details-sheet__handle"
          aria-hidden="true"
        />
        <div class="pokemon-details-sheet__head">
          <SpeciesPortrait
            class="pokemon-details-sheet__portrait"
            :url="pokemon.portrait"
            :name="pokemon.name"
          />
          <SheetHeader class="pokemon-details-sheet__header">
            <SheetTitle class="pokemon-details-sheet__title">
              <PokemonDisplayName
                :name="pokemon.name"
                :gender="pokemon.gender"
              />
            </SheetTitle>
            <SheetDescription class="pokemon-details-sheet__description">
              Lv {{ pokemon.level }} · HP {{ pokemon.currentHp }} /
              {{ pokemon.maxHp }}
            </SheetDescription>
          </SheetHeader>
          <SheetClose as-child>
            <Button
              type="button"
              variant="ghost"
              size="icon"
              class="pokemon-details-sheet__close-button"
              data-test="pokemon-details-sheet__close-button"
              aria-label="Close"
            >
              <IconX
                class="size-5"
                aria-hidden="true"
              />
            </Button>
          </SheetClose>
        </div>
      </div>
      <PokemonDetails
        :pokemon="pokemon"
        is-large
      />
    </SheetContent>
  </Sheet>
</template>

<script setup lang="ts">
import { IconX } from '@tabler/icons-vue';
import { useSwipe } from '@vueuse/core';
import { useTemplateRef } from 'vue';
import PokemonDisplayName from '@/shared/components/game/PokemonDisplayName.vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Sheet,
  SheetClose,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from '@/shared/components/ui/sheet';
import type { BattlePokemonDetails } from '../../types/page-props/BattleShowPageProps';
import PokemonDetails from './PokemonDetails.vue';

defineProps<{
  pokemon: BattlePokemonDetails;
}>();

const isOpen = defineModel<boolean>('open', { required: true });

const SWIPE_DISTANCE_TO_CLOSE_IN_PX = 60;

const { lengthY } = useSwipe(useTemplateRef('swipeArea'), {
  onSwipeEnd: (_event, direction) => {
    if (
      direction === 'down' &&
      -lengthY.value >= SWIPE_DISTANCE_TO_CLOSE_IN_PX
    ) {
      isOpen.value = false;
    }
  },
});
</script>

<style scoped>
@reference '@css/app.css';

:global(.pokemon-details-sheet) {
  @apply max-h-[85vh] gap-3 overflow-y-auto rounded-t-2xl px-4 pt-2.5 pb-[max(1.25rem,env(safe-area-inset-bottom))];
}

.pokemon-details-sheet__swipe-area {
  @apply flex touch-none flex-col gap-3;
}

.pokemon-details-sheet__handle {
  @apply h-1 w-10 shrink-0 self-center rounded-full bg-muted-foreground/40;
}

.pokemon-details-sheet__head {
  @apply flex items-center gap-3;
}

.pokemon-details-sheet__portrait {
  @apply size-14 shrink-0;
}

.pokemon-details-sheet__header {
  @apply min-w-0 flex-1 gap-0.5 p-0;
}

.pokemon-details-sheet__title {
  @apply leading-snug wrap-anywhere;
}

.pokemon-details-sheet__description {
  @apply tabular-nums;
}

.pokemon-details-sheet__close-button {
  @apply size-10 shrink-0 text-muted-foreground;
}
</style>
