<template>
  <Tooltip v-if="isLastInParty">
    <TooltipTrigger as-child>
      <span tabindex="-1">
        <Button
          type="button"
          variant="outline"
          data-test="pokemon-remove-from-party-button"
          disabled
        >
          Remove from party
        </Button>
      </span>
    </TooltipTrigger>
    <TooltipContent>Your party needs at least one Pokémon</TooltipContent>
  </Tooltip>
  <div
    v-else
    class="contents"
  >
    <Button
      type="button"
      variant="outline"
      data-test="pokemon-remove-from-party-button"
      :disabled="isRequestRunning"
      @click="isRemoveDialogOpen = true"
    >
      Remove from party
    </Button>
    <PartyRemoveDialog
      v-model:open="isRemoveDialogOpen"
      :pokemon="page.props.pokemon"
    />
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/shared/components/ui/button';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import { useIsRequestRunning } from '@/shared/composables/useIsRequestRunning';
import PartyRemoveDialog from '../../../components/PartyRemoveDialog.vue';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

const page = usePage<BoxShowPageProps>();

const isRemoveDialogOpen = ref(false);

const isRequestRunning = useIsRequestRunning();

const isLastInParty = computed(() => page.props.party.length <= 1);
</script>
