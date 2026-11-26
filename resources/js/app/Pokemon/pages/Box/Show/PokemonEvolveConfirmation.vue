<template>
  <div
    class="flex flex-col gap-4"
    data-test="pokemon-evolve-confirmation"
  >
    <DialogHeader>
      <DialogTitle>
        Evolve {{ page.props.pokemon.name }} into
        {{ option.species.displayName }}?
      </DialogTitle>
      <DialogDescription>This can't be undone.</DialogDescription>
    </DialogHeader>
    <div class="flex items-center justify-center gap-4">
      <SpeciesPortrait
        class="size-11"
        :url="page.props.pokemon.portrait"
        :name="page.props.pokemon.name"
      />
      <IconArrowNarrowRight class="text-muted-foreground" />
      <SpeciesPortrait
        class="size-11"
        :url="option.species.portrait"
        :name="option.species.displayName"
      />
    </div>
    <DialogFooter>
      <Button
        type="button"
        variant="outline"
        data-test="pokemon-evolve-confirmation__back-button"
        @click="emit('back')"
      >
        Back
      </Button>
      <Button
        type="button"
        data-test="pokemon-evolve-confirmation__evolve-button"
        :disabled="isEvolving"
        @click="emit('evolve')"
      >
        Evolve
      </Button>
    </DialogFooter>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconArrowNarrowRight } from '@tabler/icons-vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { Button } from '@/shared/components/ui/button';
import {
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/shared/components/ui/dialog';
import type {
  BoxShowPageProps,
  PokemonEvolutionOption,
} from '../../../types/page-props/BoxShowPageProps';

defineProps<{
  option: PokemonEvolutionOption;
  isEvolving: boolean;
}>();

const emit = defineEmits<{
  back: [];
  evolve: [];
}>();

const page = usePage<BoxShowPageProps>();
</script>
