<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <div class="mx-auto w-full max-w-6xl pr-10 pb-4">
        <div class="flex h-12 items-center gap-2">
          <DashboardBreadcrumbs />
        </div>
        <div class="space-y-18">
          <slot />
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import DashboardBreadcrumbs from '@/components/DashboardBreadcrumbs.vue';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
</script>
