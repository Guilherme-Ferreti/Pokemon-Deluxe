<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button
        type="button"
        variant="outline"
        size="lg"
        class="switch-pokemon-dialog__open-button"
        data-test="switch-pokemon-dialog__open-button"
        :disabled="disabled || !canSwitch"
      >
        <IconArrowsExchange aria-hidden="true" />
        Switch Pokémon
      </Button>
    </DialogTrigger>
    <DialogContent data-test="switch-pokemon-dialog">
      <DialogHeader>
        <DialogTitle>Switch Pokémon</DialogTitle>
        <DialogDescription>
          Switching uses your turn: your new Pokémon comes out first, then the
          opponent attacks it.
        </DialogDescription>
      </DialogHeader>
      <BattleTeamRadioGroup
        v-model="form.combatantIndex"
        test-id-block="switch-pokemon-dialog"
      />
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
          data-test="switch-pokemon-dialog__switch-button"
          :disabled="form.combatantIndex === null || form.processing"
          @click="switchPokemon"
        >
          Switch
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconArrowsExchange } from '@tabler/icons-vue';
import { computed, ref, watch } from 'vue';
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
import BattleTeamRadioGroup from './BattleTeamRadioGroup.vue';

defineProps<{
  disabled: boolean;
}>();

const page = usePage<BattleShowPageProps>();

const isOpen = ref(false);

const form = useForm<{ combatantIndex: string | null }>({
  combatantIndex: null,
});

watch(isOpen, () => {
  form.combatantIndex = null;
});

const canSwitch = computed(() =>
  page.props.player.team.some(
    (member) => !member.isActive && !member.isFainted,
  ),
);

function switchPokemon(): void {
  form
    .transform((data) => ({ combatantIndex: Number(data.combatantIndex) }))
    .post(route('battle.web.switch'), {
      preserveScroll: true,
      onSuccess: () => {
        isOpen.value = false;
      },
    });
}
</script>

<style scoped>
@reference '@css/app.css';

.switch-pokemon-dialog__open-button {
  @apply w-full;
}
</style>
