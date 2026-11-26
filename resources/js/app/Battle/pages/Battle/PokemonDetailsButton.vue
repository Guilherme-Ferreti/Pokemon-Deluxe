<template>
  <div class="flex shrink-0">
    <HoverCard
      :open="isHoverCardOpen"
      :open-delay="200"
      :close-delay="150"
      @update:open="(open) => (isHoverCardOpen = open && !isPhoneLayout)"
    >
      <HoverCardTrigger as-child>
        <button
          type="button"
          class="flex size-10 cursor-pointer items-center justify-center rounded-lg transition-shadow duration-150 outline-none hover:ring-2 hover:ring-muted-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 data-[state=open]:ring-2 data-[state=open]:ring-muted-foreground motion-reduce:transition-none"
          :aria-haspopup="isPhoneLayout ? 'dialog' : undefined"
          :aria-expanded="isPhoneLayout ? isSheetOpen : isHoverCardOpen"
          :data-test="testId"
          :aria-label="`${pokemon.name}'s types, boost and moves`"
          @click="openDetails"
        >
          <SpeciesPortrait
            class="size-9"
            :class="{
              'opacity-50 grayscale in-data-disabled:opacity-100': isFainted,
            }"
            :url="pokemon.portrait"
            :name="pokemon.name"
          />
        </button>
      </HoverCardTrigger>
      <PokemonDetailsHoverCard :pokemon="pokemon" />
    </HoverCard>
    <PokemonDetailsSheet
      v-model:open="isSheetOpen"
      :pokemon="pokemon"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { HoverCard, HoverCardTrigger } from '@/shared/components/ui/hover-card';
import { useBattleLayout } from '../../composables/useBattleLayout';
import type { BattlePokemonDetails } from '../../types/page-props/BattleShowPageProps';
import PokemonDetailsHoverCard from './PokemonDetailsHoverCard.vue';
import PokemonDetailsSheet from './PokemonDetailsSheet.vue';

defineProps<{
  pokemon: BattlePokemonDetails;
  isFainted?: boolean;
  testId: string;
}>();

const { isPhoneLayout } = useBattleLayout();

const isHoverCardOpen = ref(false);

const isSheetOpen = ref(false);

watch(isPhoneLayout, (isPhone) => {
  if (isPhone) {
    isHoverCardOpen.value = false;
  } else {
    isSheetOpen.value = false;
  }
});

function openDetails(): void {
  if (isPhoneLayout.value) {
    isSheetOpen.value = true;
  } else {
    isHoverCardOpen.value = true;
  }
}
</script>
