<template>
  <AlertDialog v-model:open="isOpen">
    <AlertDialogTrigger as-child>
      <Button
        type="button"
        variant="outline"
        class="border-destructive/40 text-destructive hover:bg-destructive/10 hover:text-destructive"
        data-test="battle-forfeit-button"
      >
        {{ label }}
      </Button>
    </AlertDialogTrigger>
    <AlertDialogContent data-test="battle-forfeit-button__dialog">
      <AlertDialogHeader>
        <AlertDialogTitle>{{ title }}</AlertDialogTitle>
        <AlertDialogDescription>{{ description }}</AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>Keep fighting</AlertDialogCancel>
        <AlertDialogAction
          variant="destructive"
          data-test="battle-forfeit-button__confirm-button"
          :disabled="form.processing"
          @click.prevent="forfeit"
        >
          {{ label }}
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
import { BattleType } from '@/shared/types/enums';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

const isOpen = ref(false);

const form = useForm({});

const isWild = computed(() => page.props.battle.type === BattleType.WILD);

const label = computed(() => (isWild.value ? 'Run' : 'Forfeit'));

const title = computed(() =>
  isWild.value
    ? `Run from the wild ${page.props.opponent.name}?`
    : 'Forfeit this battle?',
);

const description = computed(() =>
  isWild.value
    ? "You won't get anything for this battle. Your Pokémon are healed either way."
    : 'Forfeiting counts as a loss: no money, but your Pokémon keep the XP for opponents they knocked out.',
);

function forfeit(): void {
  form.post(route('battle.web.forfeit'), {
    preserveScroll: true,
    onSuccess: () => {
      isOpen.value = false;
    },
  });
}
</script>
