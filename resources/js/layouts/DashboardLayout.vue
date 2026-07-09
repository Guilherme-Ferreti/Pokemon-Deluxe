<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <div
        class="flex h-12 shrink-0 items-center gap-2 transition-[width,height] ease-linear"
      >
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger class="-ml-1" />
          <Separator
            orientation="vertical"
            class="mr-2 data-[orientation=vertical]:h-4"
          />
          <DashboardBreadcrumbs />
        </div>
      </div>
      <div class="mx-auto w-full max-w-6xl space-y-18 px-8 pb-4">
        <slot />
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>

<script lang="ts">
export const description = 'A sidebar that collapses to icons.';
export const iframeHeight = '800px';
export const containerClass = 'w-full h-full';
</script>

<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import DashboardBreadcrumbs from '@/components/DashboardBreadcrumbs.vue';
import { Separator } from '@/components/ui/separator';
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from '@/components/ui/sidebar';
</script>
