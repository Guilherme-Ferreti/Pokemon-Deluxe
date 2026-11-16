<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button
        type="button"
        variant="destructive"
        data-test="run-battle-dialog__open-button"
      >
        Run
      </Button>
    </DialogTrigger>
    <DialogContent data-test="run-battle-dialog">
      <DialogHeader>
        <DialogTitle
          >Run from the wild {{ page.props.opponent.name }}?</DialogTitle
        >
        <DialogDescription>
          You won't get anything for this battle. Your Pokémon are healed either
          way.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <DialogClose as-child>
          <Button
            type="button"
            variant="outline"
          >
            Keep fighting
          </Button>
        </DialogClose>
        <Button
          type="button"
          variant="destructive"
          data-test="run-battle-dialog__run-button"
          :disabled="form.processing"
          @click="run"
        >
          Run
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/shared/components/ui/dialog';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const page = usePage<BattleShowPageProps>();

const isOpen = ref(false);

const form = useForm({});

function run(): void {
  form.post(route('battle.web.forfeit'), {
    preserveScroll: true,
    onSuccess: () => {
      isOpen.value = false;
    },
  });
}
</script>
