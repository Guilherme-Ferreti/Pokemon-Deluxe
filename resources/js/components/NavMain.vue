<template>
  <SidebarGroup>
    <SidebarMenu>
      <SidebarMenuItem
        v-for="item in items"
        :key="item.title"
      >
        <SidebarMenuButton
          :tooltip="item.title"
          :data-active="getBasePath($page.url) === getBasePath(item.url)"
          as-child
        >
          <Link
            :href="item.url"
            :aria-current="
              getBasePath($page.url) === getBasePath(item.url)
                ? 'page'
                : undefined
            "
          >
            <component
              :is="item.icon"
              v-if="item.icon"
            />
            <span>{{ item.title }}</span>
          </Link>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  </SidebarGroup>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
  IconHome,
  IconPokeball,
  IconSwords,
  IconTrees,
} from '@tabler/icons-vue';
import {
  SidebarGroup,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import { getBasePath } from '@/lib/utils';
import { route } from '@/types/helpers/route';

const items = [
  {
    title: 'Dashboard',
    url: route('web.dashboard.home'),
    icon: IconHome,
  },
  {
    title: 'Wild Areas',
    url: route('web.auth.login.create'),
    icon: IconTrees,
  },
  {
    title: 'Your Pokemon',
    url: route('web.pokemon.index'),
    icon: IconPokeball,
  },
  {
    title: 'Battle',
    url: '#',
    icon: IconSwords,
  },
];
</script>
