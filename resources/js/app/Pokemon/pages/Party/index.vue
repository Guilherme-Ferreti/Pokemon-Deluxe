<template>
  <Head title="Your Party" />
  <AppPageHeader
    title="Your Party"
    description="Manage your party."
  />
  <AppSection>
    <PartyList />
  </AppSection>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppSection from '@/shared/components/content/AppSection.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import PartyList from './PartyList.vue';
</script>
