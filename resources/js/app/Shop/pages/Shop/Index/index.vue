<template>
  <Head title="Shop" />
  <AppPageHeader
    title="Shop"
    description="Buy balls, evolution items and today's TMs."
  >
    <PlayerMoney />
  </AppPageHeader>
  <ShopTabs />
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PlayerMoney from '@/shared/components/game/PlayerMoney.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import ShopTabs from './ShopTabs.vue';
</script>
