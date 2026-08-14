<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <div
        class="mx-auto w-full space-y-8 py-6 pr-10"
        :class="{
          'max-w-4xl': pageSize === 'small',
          'max-w-6xl': pageSize === 'default',
        }"
      >
        <slot />
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';

interface DashboardLayoutProps {
  pageSize?: 'small' | 'default';
}

withDefaults(defineProps<DashboardLayoutProps>(), {
  pageSize: 'default',
});
</script>
