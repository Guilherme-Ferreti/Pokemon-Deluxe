<template>
  <Head title="Dashboard" />
  <AppPageHeader
    title="Dashboard"
    :description="`Welcome back, ${page.props.auth.user.name}!`"
  />
</template>

<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AppPageHeader from '@/components/AppPageHeader.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

const page = usePage();

const { setBreadcrumbs } = useBreadcrumbs();
setBreadcrumbs([]);

defineOptions({
  layout: DashboardLayout,
});
</script>
