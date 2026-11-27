<template>
  <div class="contents">
    <Button
      type="button"
      variant="outline"
      data-test="pokemon-add-to-party-button"
      :disabled="form.processing"
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
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/shared/components/ui/button';
import { route } from '@/shared/types/helpers/route';
import PartySwapDialog from '../../../components/PartySwapDialog.vue';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

const page = usePage<BoxShowPageProps>();

const isSwapDialogOpen = ref(false);

const isPartyFull = computed(
  () => page.props.party.length >= page.props.maxPartySize,
);

const form = useForm({});

const addToParty = () => {
  if (isPartyFull.value) {
    isSwapDialogOpen.value = true;

    return;
  }

  form.post(
    route('pokemon.web.party.add', { pokemon: page.props.pokemon.id }),
    { preserveScroll: true },
  );
};
</script>
