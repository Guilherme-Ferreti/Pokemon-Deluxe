<template>
  <IncubatorCard
    :data-test="`incubator-${incubator.index}-card--incubating`"
    :index="incubator.index"
    :badge="{
      icon: IconClock,
      text: 'Incubating',
    }"
  >
    <template #picture>
      <PokemonEggSprite class="mx-auto" />
    </template>
    <p class="text-center text-sm text-muted-foreground">
      What could be inside?
    </p>
    <template #footer>
      <AppCountdown
        v-if="incubator.incubationStartedAt && incubator.readyToHatchAt"
        :data-test="`incubator-${incubator.index}-card--incubating__countdown`"
        :start-date="new Date(incubator.incubationStartedAt)"
        :end-date="new Date(incubator.readyToHatchAt)"
        @complete="handleCountdownComplete"
      />
    </template>
  </IncubatorCard>
</template>

<script setup lang="ts">
import { router, usePoll } from '@inertiajs/vue3';
import { IconClock } from '@tabler/icons-vue';
import AppCountdown from '@/shared/components/content/AppCountdown.vue';
import PokemonEggSprite from '@/shared/components/game/PokemonEggSprite.vue';
import type { Incubator } from '../../types/page-props/NurseryPageProps';
import IncubatorCard from './IncubatorCard.vue';

defineProps<{
  incubator: Incubator;
}>();

// The countdown runs on the browser clock, which may be ahead of the server's.
// Keep reloading until the server reports the egg as ready and this card is replaced.
const { start: startPollingIncubators } = usePoll(
  3000,
  { only: ['incubators'], preserveErrors: true },
  { autoStart: false },
);

function handleCountdownComplete() {
  router.reload({
    only: ['incubators'],
    preserveErrors: true,
  });

  startPollingIncubators();
}
</script>
