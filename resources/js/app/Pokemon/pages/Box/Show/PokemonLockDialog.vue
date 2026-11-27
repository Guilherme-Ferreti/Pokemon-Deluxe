<template>
  <Button
    v-if="page.props.pokemon.isLocked"
    type="button"
    variant="ghost"
    data-test="pokemon-lock-dialog__unlock-button"
    :disabled="isRequestRunning"
    @click="setLock(false)"
  >
    <IconLock />
    Locked
  </Button>
  <AlertDialog
    v-else
    v-model:open="isOpen"
  >
    <AlertDialogTrigger as-child>
      <Button
        type="button"
        variant="ghost"
        data-test="pokemon-lock-dialog__open-button"
      >
        <IconLockOpen />
        Unlocked
      </Button>
    </AlertDialogTrigger>
    <AlertDialogContent data-test="pokemon-lock-dialog">
      <AlertDialogHeader>
        <AlertDialogTitle>Lock {{ page.props.pokemon.name }}?</AlertDialogTitle>
        <AlertDialogDescription>
          A locked Pokémon can't be released or traded until you unlock it. You
          can still battle with it, train it and use items on it.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>Not now</AlertDialogCancel>
        <AlertDialogAction
          data-test="pokemon-lock-dialog__lock-button"
          :disabled="isRequestRunning"
          @click.prevent="setLock(true)"
        >
          Lock
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconLock, IconLockOpen } from '@tabler/icons-vue';
import { ref } from 'vue';
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
import { useIsRequestRunning } from '@/shared/composables/useIsRequestRunning';
import { route } from '@/shared/types/helpers/route';
import type { BoxShowPageProps } from '../../../types/page-props/BoxShowPageProps';

const page = usePage<BoxShowPageProps>();

const isOpen = ref(false);

const form = useForm({ isLocked: false });

const isRequestRunning = useIsRequestRunning();

const setLock = (isLocked: boolean) => {
  form.isLocked = isLocked;

  form.post(route('pokemon.web.lock', { pokemon: page.props.pokemon.id }), {
    preserveScroll: true,
    onSuccess: () => (isOpen.value = false),
  });
};
</script>
