<template>
  <IncubatorCard
    :index="index"
    :status="IncubatorStatus.IDLE"
    :badge="{ icon: IconCircleDashed, text: 'Idle' }"
  >
    <template #picture>
      <IconPokeball class="mx-auto size-12 opacity-20" />
    </template>
    <p class="text-center text-sm text-muted-foreground">
      This incubator is empty
    </p>
    <template #footer>
      <Button
        type="button"
        variant="outline"
        size="sm"
        data-test="incubator-card__place-egg-button"
        :disabled="form.processing"
        @click="handlePlaceEgg"
      >
        Place egg
      </Button>
    </template>
  </IncubatorCard>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { IconCircleDashed, IconPokeball } from '@tabler/icons-vue';
import { Button } from '@/shared/components/ui/button';
import { route } from '@/shared/types/helpers/route';
import { IncubatorStatus } from '../../types/enums/IncubatorStatus';
import type { Incubator } from '../../types/page-props/NurseryPageProps';
import IncubatorCard from './IncubatorCard.vue';

const props = defineProps<{
  index: Incubator['index'];
}>();

const form = useForm();

function handlePlaceEgg() {
  form.post(
    route('pokemon.web.incubators.place-egg', {
      incubatorIndex: props.index,
    }),
    {
      preserveScroll: true,
      only: ['incubators'],
    },
  );
}
</script>
