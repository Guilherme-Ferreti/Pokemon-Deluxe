<template>
  <Head title="Nursery" />
  <AppPageHeader
    title="Nursery"
    description="Manage your Pokémon eggs."
  >
    <span class="flex items-center gap-2 text-sm font-medium">
      <IconCoin
        class="size-4"
        aria-hidden="true"
      />
      <span class="sr-only">Your money:</span>
      <MoneyAmount :amount="page.props.auth.user?.money ?? 0" />
    </span>
  </AppPageHeader>
  <AppSection>
    <AppSectionHeading
      title="Your incubators"
      description="Click on an empty incubator to place an egg, then hatch it when it is ready."
    />
    <IncubatorsGrid />
  </AppSection>
  <AppSection>
    <AppSectionHeading
      title="History"
      description="See the history of all Pokémon born in your nursery."
    />
  </AppSection>
</template>

<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { IconCoin } from '@tabler/icons-vue';
import AppSection from '@/shared/components/content/AppSection.vue';
import AppSectionHeading from '@/shared/components/content/AppSectionHeading.vue';
import MoneyAmount from '@/shared/components/game/MoneyAmount.vue';
import AppPageHeader from '@/shared/components/layout/AppPageHeader.vue';
import IncubatorsGrid from './IncubatorsGrid.vue';

const page = usePage();
</script>
