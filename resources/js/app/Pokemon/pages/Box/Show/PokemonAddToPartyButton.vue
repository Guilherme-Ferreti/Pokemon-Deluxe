<template>
  <div class="contents">
    <Button
      type="button"
      variant="outline"
      data-test="pokemon-add-to-party-button"
      :disabled="isRequestRunning"
      @click="addToParty"
    >
      Add to party
    </Button>
    <PartySwapDialog
      v-model:open="isSwapDialogOpen"
      :pokemon="page.props.pokemon"
      :party="page.props.party"
    />
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Button } from '@/shared/components/ui/button';
import { useIsRequestRunning } from '@/shared/composables/useIsRequestRunning';
import PartySwapDialog from '../../../components/PartySwapDialog.vue';
import { useAddToParty } from '../../../composables/useAddToParty';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

const page = usePage<BoxShowPageProps>();

const { isSwapDialogOpen, addToParty } = useAddToParty(
  () => page.props.pokemon.id,
  () => page.props.party.length >= page.props.maxPartySize,
);

const isRequestRunning = useIsRequestRunning();
</script>
