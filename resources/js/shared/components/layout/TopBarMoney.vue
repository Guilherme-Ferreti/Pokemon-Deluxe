<template>
  <div
    v-if="isMobile"
    class="flex h-12 items-center justify-end gap-2 pr-4 text-sm font-medium"
    data-test="top-bar-money"
  >
    <IconCoin
      class="size-4"
      aria-hidden="true"
    />
    <span class="sr-only">Your money:</span>
    <MoneyAmount :amount="page.props.auth.user?.money ?? 0" />
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconCoin } from '@tabler/icons-vue';
import MoneyAmount from '@/shared/components/game/MoneyAmount.vue';
import { useSidebar } from '@/shared/components/ui/sidebar';

const page = usePage();
const { isMobile } = useSidebar();
</script>
