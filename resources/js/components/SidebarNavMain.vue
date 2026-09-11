<template>
  <SidebarGroup>
    <SidebarMenu>
      <template
        v-for="item in items"
        :key="item.title"
      >
        <SidebarMenuItem v-if="!item.subItems">
          <SidebarMenuButton
            as-child
            :tooltip="item.title"
            :data-active="isCurrentPage(item.url)"
          >
            <AppNavLink
              :title="item.title"
              :url="item.url"
              :icon="item.icon"
            />
          </SidebarMenuButton>
        </SidebarMenuItem>
        <Collapsible
          v-else
          as-child
          class="group/collapsible"
          :default-open="
            isAnyCurrentPage(item.subItems.map((subItem) => subItem.url))
          "
        >
          <SidebarMenuItem>
            <CollapsibleTrigger as-child>
              <SidebarMenuButton :tooltip="item.title">
                <component
                  :is="item.icon"
                  v-if="item.icon"
                />
                <span>{{ item.title }}</span>
                <IconChevronRight
                  class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                />
              </SidebarMenuButton>
            </CollapsibleTrigger>
            <CollapsibleContent>
              <SidebarMenuSub>
                <SidebarMenuSubItem
                  v-for="subItem in item.subItems"
                  :key="subItem.title"
                >
                  <SidebarMenuSubButton
                    as-child
                    :data-active="isCurrentPage(subItem.url)"
                  >
                    <AppNavLink
                      :title="subItem.title"
                      :url="subItem.url"
                      :icon="subItem.icon"
                    />
                  </SidebarMenuSubButton>
                </SidebarMenuSubItem>
              </SidebarMenuSub>
            </CollapsibleContent>
          </SidebarMenuItem>
        </Collapsible>
      </template>
    </SidebarMenu>
  </SidebarGroup>
</template>

<script setup lang="ts">
import {
  IconArchive,
  IconBook,
  IconChevronRight,
  IconHome,
  IconPokeball,
  IconSword,
  IconSwords,
  IconTrees,
} from '@tabler/icons-vue';
import AppNavLink from '@/components/AppNavLink.vue';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
  SidebarGroup,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubItem,
  SidebarMenuSubButton,
} from '@/components/ui/sidebar';
import { isCurrentPage } from '@/lib/utils';
import { route } from '@/types/helpers/route';

const items = [
  {
    title: 'Dashboard',
    url: route('dashboard.web.home'),
    icon: IconHome,
  },
  {
    title: 'Wild Areas',
    url: '#',
    icon: IconTrees,
  },
  {
    title: 'Your Pokemon',
    url: undefined,
    icon: IconPokeball,
    subItems: [
      {
        title: 'Party',
        url: route('pokemon.web.party'),
        icon: IconPokeball,
      },
      {
        title: 'Box',
        url: route('pokemon.web.box.index'),
        icon: IconArchive,
      },
    ],
  },
  {
    title: 'Battle',
    url: '#',
    icon: IconSwords,
  },
  {
    title: 'Pokédex',
    url: route('dex.web.pokedex.index'),
    icon: IconBook,
  },
  {
    title: 'Attackdex',
    url: route('dex.web.attackdex.index'),
    icon: IconSword,
  },
];

function isAnyCurrentPage(urls: string[]) {
  return urls.some((url) => isCurrentPage(url));
}
</script>
