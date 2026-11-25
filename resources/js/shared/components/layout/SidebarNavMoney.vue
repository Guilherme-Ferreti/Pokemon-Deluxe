<template>
  <SidebarMenu data-test="sidebar-nav-money">
    <SidebarMenuItem>
      <SidebarMenuButton
        as="div"
        class="cursor-default hover:bg-transparent active:bg-transparent"
        :tooltip="`Your money: ${formatMoney(money)}`"
      >
        <IconCoin aria-hidden="true" />
        <MoneyAmount
          class="font-medium"
          :amount="money"
        />
      </SidebarMenuButton>
    </SidebarMenuItem>
  </SidebarMenu>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { IconCoin } from '@tabler/icons-vue';
import { computed } from 'vue';
import MoneyAmount from '@/shared/components/game/MoneyAmount.vue';
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/shared/components/ui/sidebar';
import { formatMoney } from '@/shared/lib/utils';

const page = usePage();

const money = computed(() => page.props.auth.user?.money ?? 0);
</script>
