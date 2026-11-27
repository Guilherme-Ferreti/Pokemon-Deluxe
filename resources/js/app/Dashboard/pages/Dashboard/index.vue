<template>
  <Head title="Dashboard" />
  <AppPageHeader
    title="Dashboard"
    :description="`Welcome back, ${$page.props.auth.user?.name}!`"
  />
</template>

<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import { route } from '@/shared/types/helpers/route';

setLayoutProps({
  breadcrumbs: [
    {
      name: 'Dashboard',
      href: route('dashboard.web.home'),
    },
    {
      name: 'Overview',
    },
  ],
});
</script>
