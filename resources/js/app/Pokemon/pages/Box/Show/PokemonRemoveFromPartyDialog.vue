<template>
  <Tooltip v-if="isLastInParty">
    <TooltipTrigger as-child>
      <span tabindex="-1">
        <Button
          type="button"
          variant="outline"
          data-test="pokemon-remove-from-party-dialog__open-button"
          disabled
        >
          Remove from party
        </Button>
      </span>
    </TooltipTrigger>
    <TooltipContent>Your party needs at least one Pokémon</TooltipContent>
  </Tooltip>
  <AlertDialog
    v-else
    v-model:open="isOpen"
  >
    <AlertDialogTrigger as-child>
      <Button
        type="button"
        variant="outline"
        data-test="pokemon-remove-from-party-dialog__open-button"
      >
        Remove from party
      </Button>
    </AlertDialogTrigger>
    <AlertDialogContent data-test="pokemon-remove-from-party-dialog">
      <AlertDialogHeader>
        <AlertDialogTitle>
          Remove {{ page.props.pokemon.name }} from your party?
        </AlertDialogTitle>
        <AlertDialogDescription>
          It goes to your Box. You can add it back any time.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>Keep in party</AlertDialogCancel>
        <AlertDialogAction
          data-test="pokemon-remove-from-party-dialog__remove-button"
          :disabled="form.processing"
          @click.prevent="removeFromParty"
        >
          Remove
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/shared/components/ui/alert-dialog';
import { Button } from '@/shared/components/ui/button';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import { route } from '@/shared/types/helpers/route';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

const page = usePage<BoxShowPageProps>();

const isOpen = ref(false);

const isLastInParty = computed(() => page.props.party.length <= 1);

const form = useForm({});

const removeFromParty = () => {
  form.delete(
    route('pokemon.web.party.remove', { pokemon: page.props.pokemon.id }),
    {
      preserveScroll: true,
      onSuccess: () => (isOpen.value = false),
    },
  );
};
</script>
