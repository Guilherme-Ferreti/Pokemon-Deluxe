<template>
  <Sidebar collapsible="icon">
    <SidebarHeader class="relative">
      <SidebarHeaderLogo />
    </SidebarHeader>
    <Separator orientation="horizontal" />
    <SidebarContent>
      <SidebarNavMain />
    </SidebarContent>
    <SidebarFooter>
      <SidebarNavUser />
    </SidebarFooter>
  </Sidebar>
  <div
    class="sticky top-0 flex h-12 items-center px-2 peer-data-[state=collapsed]:rotate-180"
  >
    <SidebarTrigger />
  </div>
</template>

<script setup lang="ts">
import SidebarHeaderLogo from '@/components/SidebarHeaderLogo.vue';
import SidebarNavMain from '@/components/SidebarNavMain.vue';
import SidebarNavUser from '@/components/SidebarNavUser.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarTrigger,
} from '@/components/ui/sidebar';
import { Separator } from './ui/separator';
</script>
