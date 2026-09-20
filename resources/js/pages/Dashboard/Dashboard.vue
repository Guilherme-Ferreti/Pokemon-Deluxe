<template>
  <Head title="Dashboard" />
  <AppPageHeader
    title="Dashboard"
    :description="`Welcome back, ${$page.props.auth.user?.name}!`"
  />
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppPageHeader from '@/components/AppPageHeader.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

defineOptions({
  layout: DashboardLayout,
});
</script>
