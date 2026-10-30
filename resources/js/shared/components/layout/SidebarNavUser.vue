<template>
  <SidebarMenu>
    <SidebarMenuItem>
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <SidebarMenuButton
            size="lg"
            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
            data-test="sidebar__user-menu-button"
          >
            <Avatar class="size-8 rounded-lg">
              <AvatarImage
                :src="user.avatar"
                :alt="user.name"
              />
              <AvatarFallback class="rounded-lg">
                {{ user.initials }}
              </AvatarFallback>
            </Avatar>
            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-medium">{{ user.name }}</span>
              <span class="truncate text-xs">{{ user.email }}</span>
            </div>
            <IconSelector class="ml-auto size-4" />
          </SidebarMenuButton>
        </DropdownMenuTrigger>
        <DropdownMenuContent
          class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
          align="end"
          :side="isMobile ? 'bottom' : 'right'"
          :side-offset="4"
        >
          <DropdownMenuLabel class="p-0 font-normal">
            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
              <Avatar class="size-8 rounded-lg">
                <AvatarImage
                  :src="user.avatar"
                  :alt="user.name"
                />
                <AvatarFallback class="rounded-lg">
                  {{ user.initials }}
                </AvatarFallback>
              </Avatar>
              <div class="grid flex-1 text-left text-sm leading-tight">
                <span class="truncate font-semibold">{{ user.name }}</span>
                <span class="truncate text-xs">{{ user.email }}</span>
              </div>
            </div>
          </DropdownMenuLabel>
          <DropdownMenuSeparator />
          <DropdownMenuGroup>
            <DropdownMenuItem as-child>
              <AppNavLink
                title="Profile"
                url="#"
                :icon="IconUser"
              />
            </DropdownMenuItem>
            <DropdownMenuSub>
              <DropdownMenuSubTrigger>
                <IconPalette />
                Theme
              </DropdownMenuSubTrigger>
              <DropdownMenuPortal>
                <DropdownMenuSubContent>
                  <DropdownMenuRadioGroup v-model="mode">
                    <DropdownMenuRadioItem value="light">
                      <IconSun />
                      Light
                    </DropdownMenuRadioItem>
                    <DropdownMenuRadioItem value="dark">
                      <IconMoon />
                      Dark
                    </DropdownMenuRadioItem>
                  </DropdownMenuRadioGroup>
                </DropdownMenuSubContent>
              </DropdownMenuPortal>
            </DropdownMenuSub>
          </DropdownMenuGroup>
          <DropdownMenuSeparator />
          <DropdownMenuItem
            variant="destructive"
            as-child
          >
            <AppNavLink
              title="Log out"
              method="post"
              as="button"
              class="w-full"
              data-test="sidebar__logout-button"
              :icon="IconLogout"
              :url="route('auth.web.logout')"
            />
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
  IconSelector,
  IconLogout,
  IconUser,
  IconPalette,
  IconSun,
  IconMoon,
} from '@tabler/icons-vue';
import { useColorMode } from '@vueuse/core';
import AppNavLink from '@/shared/components/layout/AppNavLink.vue';
import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from '@/shared/components/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuPortal,
  DropdownMenuSeparator,
  DropdownMenuSub,
  DropdownMenuSubTrigger,
  DropdownMenuTrigger,
} from '@/shared/components/ui/dropdown-menu';
import DropdownMenuRadioGroup from '@/shared/components/ui/dropdown-menu/DropdownMenuRadioGroup.vue';
import DropdownMenuRadioItem from '@/shared/components/ui/dropdown-menu/DropdownMenuRadioItem.vue';
import DropdownMenuSubContent from '@/shared/components/ui/dropdown-menu/DropdownMenuSubContent.vue';
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from '@/shared/components/ui/sidebar';
import { route } from '@/shared/types/helpers/route';

const page = usePage();
const { isMobile } = useSidebar();
const mode = useColorMode();

const user = {
  name: page.props.auth.user?.name,
  email: page.props.auth.user?.email,
  avatar: '',
  initials: (page.props.auth.user?.name ?? '')
    .split(' ')
    .map((name) => name[0])
    .join('')
    .slice(0, 2)
    .toUpperCase(),
};
</script>
