<template>
  <div
    class="flex flex-wrap items-center gap-2"
    data-test="pokemon-actions"
  >
    <Button
      v-if="canEvolve"
      type="button"
      data-test="pokemon-actions__evolve-button"
    >
      <IconSparkles />
      Evolve
    </Button>
    <PokemonBoostDialog />
    <Tooltip v-if="pokemon.inParty">
      <TooltipTrigger as-child>
        <span tabindex="-1">
          <Button
            type="button"
            variant="outline"
            data-test="pokemon-actions__remove-from-party-button"
            :disabled="isLastInParty"
          >
            Remove from party
          </Button>
        </span>
      </TooltipTrigger>
      <TooltipContent v-if="isLastInParty">
        Your party needs at least one Pokémon
      </TooltipContent>
    </Tooltip>
    <Button
      v-else
      type="button"
      variant="outline"
      data-test="pokemon-actions__add-to-party-button"
    >
      Add to party
    </Button>
    <Button
      type="button"
      variant="ghost"
      data-test="pokemon-actions__lock-button"
    >
      <IconLock v-if="pokemon.isLocked" />
      <IconLockOpen v-else />
      {{ pokemon.isLocked ? 'Locked' : 'Unlocked' }}
    </Button>
    <Tooltip>
      <TooltipTrigger as-child>
        <span
          class="@xl/hero:ml-auto"
          tabindex="-1"
        >
          <Button
            type="button"
            variant="outline"
            class="border-destructive/50 text-destructive hover:bg-destructive/10 hover:text-destructive dark:border-destructive/60"
            data-test="pokemon-actions__release-button"
            :disabled="pokemon.isLocked"
          >
            Release
          </Button>
        </span>
      </TooltipTrigger>
      <TooltipContent v-if="pokemon.isLocked">Unlock it first</TooltipContent>
    </Tooltip>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconLock, IconLockOpen, IconSparkles } from '@tabler/icons-vue';
import { computed } from 'vue';
import { Button } from '@/shared/components/ui/button';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';
import PokemonBoostDialog from './PokemonBoostDialog.vue';

const page = usePage<BoxShowPageProps>();

const pokemon = computed(() => page.props.pokemon);

const canEvolve = computed(() =>
  page.props.evolutionOptions.some((option) => option.isAllowed),
);

const isLastInParty = computed(
  () => pokemon.value.inParty && pokemon.value.partySize <= 1,
);
</script>
