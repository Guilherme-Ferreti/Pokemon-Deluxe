<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <div class="mx-auto w-full max-w-6xl py-6 pr-10">
        <div class="space-y-8">
          <slot />
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
</script>
