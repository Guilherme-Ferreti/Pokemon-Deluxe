<template>
  <Card
    class="h-full justify-between gap-4 p-4 transition-shadow hover:shadow-md"
  >
    <div class="flex items-center justify-between gap-2">
      <p class="text-xs font-bold text-muted-foreground">
        #{{ eggIncubation.eggIncubatorIndex }}
      </p>
      <Badge variant="secondary">
        <component :is="bagde.icon" />
        <span>{{ bagde.text }}</span>
      </Badge>
    </div>
    <AppSpeciesSprite
      v-if="eggIncubation.status === EggIncubationStatus.READY_TO_CAPTURE"
      class="mx-auto"
      :url="eggIncubation.pokemon?.frontSprite ?? ''"
      :name="eggIncubation.pokemon?.name ?? ''"
    />
    <img
      v-else
      class="mx-auto size-16 shrink-0"
      loading="lazy"
      src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/mystery-egg.png"
      alt="Pokémon egg"
    />
    <p class="text-center text-sm text-muted-foreground">
      {{ eggIncubationMessage }}
    </p>
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
  </Card>
</template>

<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { IconCheck, IconClock, IconConfetti } from '@tabler/icons-vue';
import { computed } from 'vue';
import type { NurseryPageProps } from '@/app/Pokemon/types/page-props/NurseryPageProps';
import AppCountdown from '@/shared/components/AppCountdown.vue';
import AppSpeciesSprite from '@/shared/components/AppSpeciesSprite.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';
import { EggIncubationStatus } from '@/shared/types/enums/EggIncubationStatus';
import { route } from '@/shared/types/helpers/route';

const props = defineProps<{
  eggIncubation: NurseryPageProps['activeEggIncubations'][number];
}>();

const form = useForm();

const bagde = computed(() => {
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
    route('pokemon.web.egg-incubations.hatch', {
      eggIncubation: props.eggIncubation.id,
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
