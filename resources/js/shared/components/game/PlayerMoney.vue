<template>
  <span
    v-if="!isMobile"
    class="flex items-center gap-2 text-sm font-medium"
  >
    <span class="sr-only">Your money:</span>
    <MoneyAmount :amount="page.props.auth.user?.money ?? 0" />
  </span>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import MoneyAmount from '@/shared/components/game/MoneyAmount.vue';
import { useSidebar } from '@/shared/components/ui/sidebar';

const page = usePage();
const { isMobile } = useSidebar();
</script>
