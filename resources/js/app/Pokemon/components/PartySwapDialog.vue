<template>
  <Dialog
    v-model:open="isOpen"
    @update:open="(open) => open && (leavingPokemonId = undefined)"
  >
    <DialogContent
      class="flex max-h-[85dvh] flex-col"
      data-test="party-swap-dialog"
    >
      <DialogHeader>
        <DialogTitle>Your party is full</DialogTitle>
        <DialogDescription>
          Choose a Pokémon to send to the Box. {{ pokemon.name }} takes its
          place.
        </DialogDescription>
      </DialogHeader>
      <RadioGroup
        v-model="leavingPokemonId"
        class="-mx-1 min-h-0 flex-1 scrollbar-thin gap-2 overflow-y-auto px-1 py-1"
        aria-label="Party Pokémon"
        data-test="party-swap-dialog__party-list"
      >
        <AppChoiceCard
          v-for="member in party"
          :key="member.id"
          :data-test="`party-swap-dialog__pokemon-${member.id}-radio`"
          :value="member.id"
        >
          <SpeciesPortrait
            class="size-10"
            :url="member.portrait"
            :name="member.name"
          />
          <span class="flex min-w-0 flex-col gap-0.5">
            <span class="text-sm font-medium">{{ member.name }}</span>
            <span class="text-xs text-muted-foreground tabular-nums">
              Lv {{ member.level }} · +{{ member.boost }}
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
          type="button"
          data-test="party-swap-dialog__swap-button"
          :disabled="!leavingPokemon || isRequestRunning"
          @click="swap"
        >
          {{ leavingPokemon ? `Swap with ${leavingPokemon.name}` : 'Swap' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppChoiceCard from '@/shared/components/form/AppChoiceCard.vue';
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
} from '@/shared/components/ui/dialog';
import { RadioGroup } from '@/shared/components/ui/radio-group';
import { useIsRequestRunning } from '@/shared/composables/useIsRequestRunning';
import { route } from '@/shared/types/helpers/route';
import type { PartyMember } from '../types/PartyMember';

const props = defineProps<{
  pokemon: { id: string; name: string };
  party: PartyMember[];
}>();

const isOpen = defineModel<boolean>('open', { default: false });

const leavingPokemonId = ref<string | undefined>();

const leavingPokemon = computed(
  () =>
    props.party.find((member) => member.id === leavingPokemonId.value) ?? null,
);

const form = useForm({ leavingPokemonId: '' });

const isRequestRunning = useIsRequestRunning();

const swap = () => {
  if (!leavingPokemon.value) {
    return;
  }

  form.leavingPokemonId = leavingPokemon.value.id;

  form.post(route('pokemon.web.party.swap', { pokemon: props.pokemon.id }), {
    preserveScroll: true,
    onSuccess: () => (isOpen.value = false),
  });
};
</script>
