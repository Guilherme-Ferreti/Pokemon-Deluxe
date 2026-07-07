<template>
  <Sidebar collapsible="icon">
    <SidebarHeader>
      <div class="flex items-center gap-2 px-2 py-2">
        <img
          src="/assets/logo-icon.png"
          alt="Pokemon Deluxe"
          class="max-w-8"
        />
        <p class="text-sm leading-tight font-semibold text-sidebar-foreground">
          Pokemon Deluxe
        </p>
      </div>
    </SidebarHeader>
    <Separator orientation="horizontal" />
    <SidebarContent>
      <NavMain />
    </SidebarContent>
    <SidebarFooter>
      <NavUser />
    </SidebarFooter>
    <SidebarRail />
  </Sidebar>
</template>

<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarRail,
} from '@/components/ui/sidebar';
import { Separator } from './ui/separator';
</script>
