<template>
  <Sidebar collapsible="icon">
    <SidebarHeader>
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
} from '@/components/ui/sidebar';
import { Separator } from './ui/separator';
</script>
