<template>
  <Tooltip v-if="!hasEvolutions">
    <TooltipTrigger as-child>
      <span tabindex="-1">
        <Button
          type="button"
          variant="outline"
          data-test="pokemon-evolve-dialog__open-button"
          disabled
        >
          <IconSparkles />
          Evolve
        </Button>
      </span>
    </TooltipTrigger>
    <TooltipContent>This Pokémon cannot evolve any further</TooltipContent>
  </Tooltip>
  <Dialog
    v-else
    v-model:open="isOpen"
    @update:open="(open) => open && resetDialog()"
  >
    <DialogTrigger as-child>
      <Button
        type="button"
        data-test="pokemon-evolve-dialog__open-button"
        :variant="canEvolve ? 'default' : 'outline'"
      >
        <IconSparkles />
        Evolve
      </Button>
    </DialogTrigger>
    <DialogContent
      class="flex max-h-[85dvh] flex-col"
      data-test="pokemon-evolve-dialog"
    >
      <PokemonEvolveConfirmation
        v-if="isConfirming && selectedOption"
        :option="selectedOption"
        :is-evolving="isEvolving"
        @back="isConfirming = false"
        @evolve="evolve(selectedOption)"
      />
      <div
        v-else
        class="flex min-h-0 flex-1 flex-col gap-4"
      >
        <DialogHeader>
          <DialogTitle>Evolve {{ pokemon.name }}</DialogTitle>
          <DialogDescription>
            <template v-if="canEvolve">Choose an evolution.</template>
            <template v-else>{{ pokemon.name }} can't evolve yet.</template>
          </DialogDescription>
        </DialogHeader>
        <RadioGroup
          v-model="selectedOptionId"
          class="-mx-1 min-h-0 flex-1 scrollbar-thin gap-2 overflow-y-auto px-1 py-1"
          aria-label="Evolutions"
          data-test="pokemon-evolve-dialog__option-list"
        >
          <AppChoiceCard
            v-for="option in page.props.evolutionOptions"
            :key="option.id"
            :data-test="`pokemon-evolve-dialog__option-${option.id}-radio`"
            :value="option.id"
            :disabled="!option.isAllowed"
          >
            <SpeciesPortrait
              class="size-10"
              :url="option.species.portrait"
              :name="option.species.displayName"
            />
            <span class="flex min-w-0 flex-col gap-1">
              <span class="text-sm font-medium">
                {{ option.species.displayName }}
              </span>
              <span
                class="text-xs text-muted-foreground"
                :data-test="`pokemon-evolve-dialog__option-${option.id}-requirement-text`"
              >
                Evolves
                <EvolutionRequirementText
                  :min-level="option.minLevel"
                  :evolution-item="option.evolutionItem"
                  :gender="option.gender"
                />
              </span>
            </span>
          </AppChoiceCard>
        </RadioGroup>
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
            v-if="canEvolve"
            type="button"
            data-test="pokemon-evolve-dialog__continue-button"
            :disabled="!selectedOption"
            @click="isConfirming = true"
          >
            Continue
          </Button>
        </DialogFooter>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { IconSparkles } from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import AppChoiceCard from '@/shared/components/form/AppChoiceCard.vue';
import EvolutionRequirementText from '@/shared/components/game/EvolutionRequirementText.vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
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
import { RadioGroup } from '@/shared/components/ui/radio-group';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import { route } from '@/shared/types/helpers/route';
import type {
  BoxShowPageProps,
  PokemonEvolutionOption,
} from '../../../types/page-props/BoxShowPageProps';
import PokemonEvolveConfirmation from './PokemonEvolveConfirmation.vue';

const page = usePage<BoxShowPageProps>();

const pokemon = computed(() => page.props.pokemon);

const isOpen = ref(false);

const isEvolving = ref(false);

const isConfirming = ref(false);

const selectedOptionId = ref<string | undefined>();

const selectedOption = computed(
  () =>
    page.props.evolutionOptions.find(
      (option) => option.id === selectedOptionId.value && option.isAllowed,
    ) ?? null,
);

const hasEvolutions = computed(() => page.props.evolutionOptions.length > 0);

const canEvolve = computed(() =>
  page.props.evolutionOptions.some((option) => option.isAllowed),
);

const resetDialog = () => {
  isConfirming.value = false;
  selectedOptionId.value = page.props.evolutionOptions.find(
    (option) => option.isAllowed,
  )?.id;
};

const evolve = (option: PokemonEvolutionOption) => {
  router.post(
    route('pokemon.web.evolve', { pokemon: pokemon.value.id }),
    { species_evolution_id: option.id },
    {
      preserveScroll: true,
      onStart: () => (isEvolving.value = true),
      onSuccess: () => (isOpen.value = false),
      onFinish: () => (isEvolving.value = false),
    },
  );
};
</script>
