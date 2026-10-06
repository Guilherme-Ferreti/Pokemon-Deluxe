<template>
  <Card
    class="h-full justify-between gap-4 p-4 transition-shadow hover:shadow-md"
  >
    <div class="flex items-center justify-between gap-2">
      <p class="text-xs font-bold text-muted-foreground">
        #{{ eggIncubationProcess.eggIncubatorIndex }}
      </p>
      <Badge variant="secondary">
        <component :is="bagde.icon" />
        <span>{{ bagde.text }}</span>
      </Badge>
    </div>
    <AppSpeciesSprite
      v-if="activeProcess === ActiveProcessStatus.READY_TO_CAPTURE"
      class="mx-auto"
      :url="eggIncubationProcess.pokemon?.frontSprite ?? ''"
      :name="eggIncubationProcess.pokemon?.name ?? ''"
    />
    <img
      v-else
      class="mx-auto size-16 shrink-0"
      loading="lazy"
      src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/mystery-egg.png"
      alt="Pokémon egg"
    />
    <p class="text-center text-sm text-muted-foreground">
      {{ activeProcessMessage }}
    </p>
    <AppCountdown
      v-if="activeProcess === ActiveProcessStatus.INCUBATING"
      :start-date="new Date(eggIncubationProcess.createdAt)"
      :end-date="new Date(eggIncubationProcess.eggReadyToHatchAt)"
      @complete="activeProcess = ActiveProcessStatus.READY_TO_HATCH"
    />
    <Button
      v-if="activeProcess === ActiveProcessStatus.READY_TO_HATCH"
      type="button"
      variant="secondary"
      size="sm"
    >
      Hatch
    </Button>
    <Button
      v-if="activeProcess === ActiveProcessStatus.READY_TO_CAPTURE"
      type="button"
      variant="secondary"
      size="sm"
    >
      Catch
    </Button>
  </Card>
</template>

<script setup lang="ts">
import { IconCheck, IconClock, IconConfetti } from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import type { NurseryPageProps } from '@/app/Pokemon/types/page-props/NurseryPageProps';
import AppCountdown from '@/shared/components/AppCountdown.vue';
import AppSpeciesSprite from '@/shared/components/AppSpeciesSprite.vue';
import { Badge } from '@/shared/components/ui/badge';
import { Button } from '@/shared/components/ui/button';
import { Card } from '@/shared/components/ui/card';

enum ActiveProcessStatus {
  INCUBATING = 'incubating',
  READY_TO_HATCH = 'readyToHatch',
  READY_TO_CAPTURE = 'readyToCapture',
}

const props = defineProps<{
  eggIncubationProcess: NurseryPageProps['activeEggIncubationProcesses'][number];
}>();

const activeProcess = ref<ActiveProcessStatus>(
  getActiveProcessStatusFromProps(),
);

const bagde = computed(() => {
  if (activeProcess.value === ActiveProcessStatus.READY_TO_HATCH) {
    return {
      icon: IconCheck,
      text: 'Ready to hatch',
    };
  }

  if (activeProcess.value === ActiveProcessStatus.READY_TO_CAPTURE) {
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

const activeProcessMessage = computed(() => {
  if (activeProcess.value === ActiveProcessStatus.READY_TO_HATCH) {
    return "It's ready to hatch!";
  }

  if (activeProcess.value === ActiveProcessStatus.READY_TO_CAPTURE) {
    return `A ${props.eggIncubationProcess.pokemon?.name} has hatched!`;
  }

  return 'What could be inside?';
});

function getActiveProcessStatusFromProps(): ActiveProcessStatus {
  if (props.eggIncubationProcess.isReadyToHatch) {
    return ActiveProcessStatus.READY_TO_HATCH;
  }

  if (props.eggIncubationProcess.isReadyToCapture) {
    return ActiveProcessStatus.READY_TO_CAPTURE;
  }

  return ActiveProcessStatus.INCUBATING;
}
</script>
