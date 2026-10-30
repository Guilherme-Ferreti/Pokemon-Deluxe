<template>
  <IncubatorCard
    :index="incubator.index"
    :status="incubator.status"
    :badge="{
      icon: IconClock,
      text: 'Incubating',
    }"
  >
    <template #picture>
      <AppPokemonEggSprite class="mx-auto" />
    </template>
    <p class="text-center text-sm text-muted-foreground">
      What could be inside?
    </p>
    <template #footer>
      <AppCountdown
        v-if="incubator.incubationStartedAt && incubator.readyToHatchAt"
        :start-date="new Date(incubator.incubationStartedAt)"
        :end-date="new Date(incubator.readyToHatchAt)"
        @complete="handleCountdownComplete"
      />
    </template>
  </IncubatorCard>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { IconClock } from '@tabler/icons-vue';
import IncubatorCard from '@/app/Pokemon/components/IncubatorCard.vue';
import type { Incubator } from '@/app/Pokemon/types/page-props/NurseryPageProps';
import AppPokemonEggSprite from '@/shared/components/AppPokemonEggSprite.vue';
import AppCountdown from '@/shared/components/content/AppCountdown.vue';

defineProps<{
  incubator: Incubator;
}>();

function handleCountdownComplete() {
  router.reload({
    only: ['incubators'],
    preserveErrors: true,
  });
}
</script>
