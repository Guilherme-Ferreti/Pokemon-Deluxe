<template>
  <Head title="Inventory" />
  <AppPageHeader
    title="Inventory"
    description="Everything you own."
  />
  <InventoryTabs />
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import InventoryTabs from './InventoryTabs.vue';
</script>
