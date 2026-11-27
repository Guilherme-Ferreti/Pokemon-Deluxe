import type { Visit } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import type { ComputedRef } from 'vue';
import { computed, ref } from 'vue';

const runningVisitCount = ref(0);

let isListening = false;

function canInterruptOtherVisits(visit: Visit): boolean {
  return !visit.async && !visit.prefetch;
}

function listenToVisits(): void {
  if (isListening) {
    return;
  }

  isListening = true;

  router.on('start', (event) => {
    if (canInterruptOtherVisits(event.detail.visit)) {
      runningVisitCount.value += 1;
    }
  });

  router.on('finish', (event) => {
    if (canInterruptOtherVisits(event.detail.visit)) {
      runningVisitCount.value = Math.max(0, runningVisitCount.value - 1);
    }
  });
}

export function useIsRequestRunning(): ComputedRef<boolean> {
  listenToVisits();

  return computed(() => runningVisitCount.value > 0);
}
