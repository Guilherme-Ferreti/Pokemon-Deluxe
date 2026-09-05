<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <div
        class="mx-auto w-full py-6 pr-10"
        :class="{
          '4xl:pr-0 max-w-4xl': pageSize === 'small',
          '6xl:pr-0 max-w-6xl': pageSize === 'default',
        }"
      >
        <AppBreadcrumbs
          v-if="breadcrumbs"
          class="mb-4"
          :breadcrumbs="breadcrumbs"
        />
        <div class="@container/main-content space-y-8">
          <slot />
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup lang="ts">
import AppBreadcrumbs from '@/components/AppBreadcrumbs.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';

interface DashboardLayoutProps {
  pageSize?: 'small' | 'default';
  breadcrumbs?: { name: string; href?: string }[];
}

withDefaults(defineProps<DashboardLayoutProps>(), {
  pageSize: 'default',
});
</script>
