<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <header
        class="flex h-12 shrink-0 items-center gap-2 transition-[width,height] ease-linear"
      >
        <div class="flex items-center gap-2 px-4">
          <SidebarTrigger class="-ml-1" />
          <Separator
            orientation="vertical"
            class="mr-2 data-[orientation=vertical]:h-4"
          />
          <Breadcrumb>
            <BreadcrumbList>
              <template
                v-for="(crumb, index) in breadcrumbs"
                :key="index"
              >
                <BreadcrumbItem>
                  <BreadcrumbLink
                    v-if="crumb.href"
                    :href="crumb.href"
                  >
                    {{ crumb.title }}
                  </BreadcrumbLink>
                  <BreadcrumbPage v-else>
                    {{ crumb.title }}
                  </BreadcrumbPage>
                </BreadcrumbItem>
                <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1" />
              </template>
            </BreadcrumbList>
          </Breadcrumb>
        </div>
      </header>
      <div class="mx-auto w-full max-w-6xl space-y-6 px-8 pb-4">
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
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Separator } from '@/components/ui/separator';
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from '@/components/ui/sidebar';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

const { breadcrumbs } = useBreadcrumbs();
</script>
