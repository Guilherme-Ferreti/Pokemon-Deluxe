<template>
  <div
    class="flex flex-wrap items-center justify-between gap-2 bg-muted px-4 py-3 text-sm"
    data-test="tm-rotation-notice"
  >
    <span class="font-semibold">Today's TMs</span>
    <span class="flex items-center gap-2">
      New TMs in
      <AppCountdown
        :start-date="new Date(startsAt)"
        :end-date="new Date(endsAt)"
        @complete="reloadTmRotation"
      />
    </span>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AppCountdown from '@/shared/components/content/AppCountdown.vue';

defineProps<{
  startsAt: string;
  endsAt: string;
}>();

const reloadTmRotation = () => {
  router.reload({ only: ['tmRotation'] });
};
</script>
