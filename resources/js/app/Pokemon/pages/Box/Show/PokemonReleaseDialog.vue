<template>
  <Tooltip v-if="page.props.pokemon.isLocked">
    <TooltipTrigger as-child>
      <span
        class="@xl/hero:ml-auto"
        tabindex="-1"
      >
        <Button
          type="button"
          variant="outline"
          data-test="pokemon-release-dialog__open-button"
          :class="releaseButtonClass"
          disabled
        >
          Release
        </Button>
      </span>
    </TooltipTrigger>
    <TooltipContent>Unlock it first</TooltipContent>
  </Tooltip>
  <AlertDialog
    v-else
    v-model:open="isOpen"
    @update:open="(open) => open && (confirmedName = '')"
  >
    <AlertDialogTrigger as-child>
      <Button
        type="button"
        variant="outline"
        data-test="pokemon-release-dialog__open-button"
        :class="['@xl/hero:ml-auto', releaseButtonClass]"
      >
        Release
      </Button>
    </AlertDialogTrigger>
    <AlertDialogContent data-test="pokemon-release-dialog">
      <AlertDialogHeader>
        <AlertDialogTitle>Release {{ pokemon.name }}?</AlertDialogTitle>
        <AlertDialogDescription>
          It leaves for good and you get nothing back.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <Alert
        v-if="warning"
        variant="destructive"
        class="border-destructive/50 bg-destructive/10 dark:border-destructive/60 dark:bg-destructive/20"
        data-test="pokemon-release-dialog__warning-text"
      >
        <IconAlertTriangle />
        <AlertDescription>{{ warning }}</AlertDescription>
      </Alert>
      <Field>
        <FieldLabel :for="confirmNameInputId">
          <span
            >Type <strong>{{ pokemon.name }}</strong> to confirm</span
          >
        </FieldLabel>
        <Input
          :id="confirmNameInputId"
          v-model="confirmedName"
          type="text"
          autocomplete="off"
          data-test="pokemon-release-dialog__confirm-name-input"
        />
      </Field>
      <AlertDialogFooter>
        <AlertDialogCancel>Keep it</AlertDialogCancel>
        <AlertDialogAction
          data-test="pokemon-release-dialog__release-button"
          :class="buttonVariants({ variant: 'destructive' })"
          :disabled="!isNameConfirmed || isRequestRunning"
          @click.prevent="release"
        >
          Release
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconAlertTriangle } from '@tabler/icons-vue';
import { computed, ref, useId } from 'vue';
import { Alert, AlertDescription } from '@/shared/components/ui/alert';
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
import { Button, buttonVariants } from '@/shared/components/ui/button';
import { Field, FieldLabel } from '@/shared/components/ui/field';
import { Input } from '@/shared/components/ui/input';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import { useIsRequestRunning } from '@/shared/composables/useIsRequestRunning';
import { route } from '@/shared/types/helpers/route';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

const releaseButtonClass =
  'border-destructive/50 text-destructive hover:bg-destructive/10 hover:text-destructive dark:border-destructive/60';

const page = usePage<BoxShowPageProps>();

const pokemon = computed(() => page.props.pokemon);

const isOpen = ref(false);

const confirmedName = ref('');

const confirmNameInputId = useId();

const isNameConfirmed = computed(
  () => confirmedName.value.trim() === pokemon.value.name,
);

const warning = computed(() => {
  const { isShiny, boost } = pokemon.value;

  if (isShiny && boost > 0) {
    return `This Pokémon is shiny and has +${boost} boost. The boost stones used on it are lost too.`;
  }

  if (isShiny) {
    return 'This Pokémon is shiny.';
  }

  if (boost > 0) {
    return `This Pokémon has +${boost} boost. The boost stones used on it are lost too.`;
  }

  return null;
});

const form = useForm({});

const isRequestRunning = useIsRequestRunning();

const release = () => {
  if (!isNameConfirmed.value) {
    return;
  }

  form.delete(route('pokemon.web.release', { pokemon: pokemon.value.id }));
};
</script>
