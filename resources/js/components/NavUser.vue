<template>
  <SidebarMenu>
    <SidebarMenuItem>
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <SidebarMenuButton
            size="lg"
            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
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
            <DropdownMenuItem>
              <Link
                href="#"
                class="flex w-full items-center gap-2"
              >
                <IconUser />
                Profile
              </Link>
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
          <DropdownMenuItem variant="destructive">
            <Link
              method="post"
              as="button"
              class="flex w-full items-center gap-2"
              :href="route('web.auth.logout')"
            >
              <IconLogout />
              Log out
            </Link>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
  IconSelector,
  IconLogout,
  IconUser,
  IconPalette,
  IconSun,
  IconMoon,
} from '@tabler/icons-vue';
import { useColorMode } from '@vueuse/core';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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
} from '@/components/ui/dropdown-menu';
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from '@/components/ui/sidebar';
import { route } from '@/types/helpers/route';
import DropdownMenuRadioGroup from './ui/dropdown-menu/DropdownMenuRadioGroup.vue';
import DropdownMenuRadioItem from './ui/dropdown-menu/DropdownMenuRadioItem.vue';
import DropdownMenuSubContent from './ui/dropdown-menu/DropdownMenuSubContent.vue';

const page = usePage();
const { isMobile } = useSidebar();
const mode = useColorMode();

const user = {
  name: page.props.auth.user.name,
  email: page.props.auth.user.email,
  avatar: page.props.auth.user.avatar ?? '',
  initials: page.props.auth.user.name
    .split(' ')
    .map((name) => name[0])
    .join('')
    .slice(0, 2)
    .toUpperCase(),
};
</script>
