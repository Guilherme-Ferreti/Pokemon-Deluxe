<template>
  <div
    class="flex h-16 items-center gap-2 px-2 py-2 group-data-[collapsible=icon]:justify-center"
  >
    <Tooltip>
      <TooltipTrigger as-child>
        <img
          src="/assets/logo-icon.png"
          alt="Pokemon Deluxe"
          class="max-w-8 group-data-[collapsible=icon]:size-6"
        />
      </TooltipTrigger>
      <TooltipContent
        side="right"
        align="center"
        :hidden="state !== 'collapsed' || isMobile"
      >
        Pokemon Deluxe
      </TooltipContent>
    </Tooltip>
    <p
      class="text-sm leading-tight font-semibold text-sidebar-foreground group-data-[collapsible=icon]:hidden"
    >
      Pokemon Deluxe
    </p>
  </div>
</template>

<script setup lang="ts">
import { useSidebar } from '@/components/ui/sidebar';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip';

const { isMobile, state } = useSidebar();
</script>
