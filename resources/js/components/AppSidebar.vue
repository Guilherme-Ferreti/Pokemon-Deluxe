<template>
  <Sidebar collapsible="icon">
    <SidebarHeader class="relative">
      <SidebarHeaderLogo />
    </SidebarHeader>
    <Separator orientation="horizontal" />
    <SidebarContent>
      <NavMain />
    </SidebarContent>
    <SidebarFooter>
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <div
    class="sticky top-0 flex h-12 items-center px-2 peer-data-[state=collapsed]:rotate-180"
  >
    <SidebarTrigger />
  </div>
</template>

<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import SidebarHeaderLogo from '@/components/SidebarHeaderLogo.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarTrigger,
} from '@/components/ui/sidebar';
import { Separator } from './ui/separator';
</script>
