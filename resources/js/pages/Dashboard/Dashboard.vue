<template>
  <Head title="Dashboard" />

  <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
  <p class="text-muted-foreground">
    Welcome back, {{ page.props.auth.user?.name }}!
  </p>
</template>

<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

const page = usePage();

defineOptions({
  layout: DashboardLayout,
});
</script>
