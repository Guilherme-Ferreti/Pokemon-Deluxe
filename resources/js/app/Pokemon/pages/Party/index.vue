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
import PartyList from '@/app/Pokemon/pages/Party/PartyList.vue';
import AppSection from '@/shared/components/content/AppSection.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
</script>
