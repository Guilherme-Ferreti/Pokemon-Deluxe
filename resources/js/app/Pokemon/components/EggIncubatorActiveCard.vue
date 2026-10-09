<template>
  <EggIncubatorCard
    :egg-incubator-index="eggIncubation.eggIncubatorIndex"
    :badge-icon="badge.icon"
    :badge-text="badge.text"
    :message="eggIncubationMessage"
  >
    <template #picture>
      <AppSpeciesSprite
        v-if="eggIncubation.status === EggIncubationStatus.READY_TO_CAPTURE"
        class="mx-auto"
        :url="eggIncubation.pokemon?.frontSprite ?? ''"
        :name="eggIncubation.pokemon?.name ?? ''"
      />
      <AppPokemonEggSprite
        v-else
        class="mx-auto"
      />
    </template>
    <template #footer>
      <AppCountdown
        v-if="eggIncubation.status === EggIncubationStatus.INCUBATING"
        :start-date="new Date(eggIncubation.createdAt)"
        :end-date="new Date(eggIncubation.readyToHatchAt)"
        @complete="handleCountdownComplete"
      />
      <Button
        v-if="eggIncubation.status === EggIncubationStatus.READY_TO_HATCH"
        type="button"
        variant="outline"
        size="sm"
        :disabled="form.processing"
        @click="handleHatchEgg"
      >
        Hatch
      </Button>
      <Button
        v-if="eggIncubation.status === EggIncubationStatus.READY_TO_CAPTURE"
        type="button"
        variant="outline"
        size="sm"
      >
        Catch
      </Button>
    </template>
  </EggIncubatorCard>
</template>

<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { IconCheck, IconClock, IconConfetti } from '@tabler/icons-vue';
import { computed } from 'vue';
import EggIncubatorCard from '@/app/Pokemon/components/EggIncubatorCard.vue';
import type { NurseryPageProps } from '@/app/Pokemon/types/page-props/NurseryPageProps';
import AppCountdown from '@/shared/components/AppCountdown.vue';
import AppPokemonEggSprite from '@/shared/components/AppPokemonEggSprite.vue';
import AppSpeciesSprite from '@/shared/components/AppSpeciesSprite.vue';
import { Button } from '@/shared/components/ui/button';
import { EggIncubationStatus } from '@/shared/types/enums';
import { route } from '@/shared/types/helpers/route';

const props = defineProps<{
  eggIncubation: NurseryPageProps['activeEggIncubations'][number];
}>();

const form = useForm();

const badge = computed(() => {
  if (props.eggIncubation.status === EggIncubationStatus.READY_TO_HATCH) {
    return {
      icon: IconCheck,
      text: 'Ready to hatch',
    };
  }

  if (props.eggIncubation.status === EggIncubationStatus.READY_TO_CAPTURE) {
    return {
      icon: IconConfetti,
      text: 'Ready to capture',
    };
  }

  return {
    icon: IconClock,
    text: 'Incubating',
  };
});

const eggIncubationMessage = computed(() => {
  if (props.eggIncubation.status === EggIncubationStatus.READY_TO_HATCH) {
    return "It's ready to hatch!";
  }

  if (props.eggIncubation.status === EggIncubationStatus.READY_TO_CAPTURE) {
    return `A ${props.eggIncubation.pokemon?.name} has hatched!`;
  }

  return 'What could be inside?';
});

function handleHatchEgg() {
  form.post(
    route('pokemon.web.egg-incubators.hatch-egg', {
      eggIncubatorIndex: props.eggIncubation.eggIncubatorIndex,
    }),
    {
      preserveScroll: true,
      only: ['activeEggIncubations'],
    },
  );
}

function handleCountdownComplete() {
  router.reload({
    only: ['activeEggIncubations'],
    preserveErrors: true,
  });
}
</script>
