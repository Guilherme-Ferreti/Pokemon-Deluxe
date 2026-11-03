<template>
  <IncubatorCard
    :data-test="`incubator-${index}-card--not-purchased`"
    :index="index"
    :badge="{ icon: IconLock, text: 'Not purchased' }"
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
        :data-test="`incubator-${index}-card--not-purchased__purchase-button`"
        :disabled="form.processing"
        @click="handlePurchase"
      >
        Purchase
      </Button>
    </template>
  </IncubatorCard>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { IconLock } from '@tabler/icons-vue';
import { Button } from '@/shared/components/ui/button';
import { route } from '@/shared/types/helpers/route';
import type { Incubator } from '../../types/page-props/NurseryPageProps';
import IncubatorCard from './IncubatorCard.vue';

const props = defineProps<{
  index: Incubator['index'];
}>();

const form = useForm();

function handlePurchase() {
  form.post(
    route('pokemon.web.incubators.purchase', {
      incubatorIndex: props.index,
    }),
    {
      preserveScroll: true,
      only: ['incubators'],
    },
  );
}
</script>
