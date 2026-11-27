<template>
  <AlertDialog v-model:open="isOpen">
    <AlertDialogContent data-test="party-remove-dialog">
      <AlertDialogHeader>
        <AlertDialogTitle>
          Remove {{ pokemon.name }} from your party?
        </AlertDialogTitle>
        <AlertDialogDescription>
          It goes to your Box. You can add it back any time.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>Keep in party</AlertDialogCancel>
        <AlertDialogAction
          data-test="party-remove-dialog__remove-button"
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
import { useForm } from '@inertiajs/vue3';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/shared/components/ui/alert-dialog';
import { route } from '@/shared/types/helpers/route';

const props = defineProps<{
  pokemon: { id: string; name: string };
}>();

const isOpen = defineModel<boolean>('open', { default: false });

const form = useForm({});

const removeFromParty = () => {
  form.delete(
    route('pokemon.web.party.remove', { pokemon: props.pokemon.id }),
    {
      preserveScroll: true,
      onSuccess: () => (isOpen.value = false),
    },
  );
};
</script>
