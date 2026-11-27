<template>
  <Head title="Shop" />
  <AppPageHeader
    title="Shop"
    description="Buy balls, evolution items and today's TMs."
  />
  <ShopTabs />
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import { route } from '@/shared/types/helpers/route';
import ShopTabs from './ShopTabs.vue';

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Shop',
      href: route('shop.web.index'),
    },
    {
      name: 'List',
    },
  ],
});
</script>
