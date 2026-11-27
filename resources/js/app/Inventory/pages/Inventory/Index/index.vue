<template>
  <Head title="Inventory" />
  <AppPageHeader
    title="Inventory"
    description="Everything you own."
  />
  <InventoryTabs />
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import { route } from '@/shared/types/helpers/route';
import InventoryTabs from './InventoryTabs.vue';

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Inventory',
      href: route('inventory.web.index'),
    },
    {
      name: 'List',
    },
  ],
});
</script>
