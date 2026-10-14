<template>
  <EggIncubatorCard
    badge-text="Empty"
    :egg-incubator-index="eggIncubatorIndex"
    :badge-icon="IconCircleDashed"
  >
    <template #picture>
      <IconPokeball class="mx-auto size-12 opacity-20" />
    </template>
    <p class="text-center text-sm text-muted-foreground">
      The incubator is empty
    </p>
    <template #footer>
      <Button
        type="button"
        variant="outline"
        size="sm"
        @click="handlePlaceEgg"
      >
        Place egg
      </Button>
    </template>
  </EggIncubatorCard>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { IconCircleDashed, IconPokeball } from '@tabler/icons-vue';
import EggIncubatorCard from '@/app/Pokemon/components/EggIncubatorCard.vue';
import { Button } from '@/shared/components/ui/button';
import { route } from '@/shared/types/helpers/route';
import type { EggIncubation } from '@/shared/types/response';

const props = defineProps<{
  eggIncubatorIndex: EggIncubation['eggIncubatorIndex'];
}>();

const form = useForm({
  eggIncubatorIndex: props.eggIncubatorIndex,
});

function handlePlaceEgg() {
  form.post(
    route('pokemon.web.egg-incubators.place-egg', {
      eggIncubatorIndex: props.eggIncubatorIndex,
    }),
    {
      preserveScroll: true,
    },
  );
}
</script>
