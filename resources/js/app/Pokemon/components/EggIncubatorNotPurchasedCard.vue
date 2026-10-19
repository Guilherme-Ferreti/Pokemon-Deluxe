<template>
  <EggIncubatorCard
    badge-text="Not purchased"
    :egg-incubator-index="eggIncubatorIndex"
    :badge-icon="IconLock"
  >
    <template #picture>
      <div
        class="mx-auto flex size-12 items-center justify-center rounded-full border-2 border-dashed border-muted-foreground/40"
      >
        <IconLock class="size-6 text-muted-foreground/70" />
      </div>
    </template>
    <p class="text-center text-sm text-muted-foreground">
      This incubator has not been purchased yet
    </p>
    <template #footer>
      <Button
        type="button"
        variant="outline"
        size="sm"
        :disabled="form.processing"
        @click="handlePurchase"
      >
        Purchase
      </Button>
    </template>
  </EggIncubatorCard>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { IconLock } from '@tabler/icons-vue';
import EggIncubatorCard from '@/app/Pokemon/components/EggIncubatorCard.vue';
import { Button } from '@/shared/components/ui/button';
import { route } from '@/shared/types/helpers/route';
import type { EggIncubation } from '@/shared/types/response';

const props = defineProps<{
  eggIncubatorIndex: EggIncubation['eggIncubatorIndex'];
}>();

const form = useForm();

function handlePurchase() {
  form.post(
    route('pokemon.web.egg-incubators.purchase', {
      eggIncubatorIndex: props.eggIncubatorIndex,
    }),
    {
      preserveScroll: true,
    },
  );
}
</script>
