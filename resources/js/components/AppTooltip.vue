<template>
  <TooltipProvider>
    <Tooltip>
      <TooltipTrigger
        as="div"
        as-child
      >
        <slot />
      </TooltipTrigger>
      <TooltipContent>
        <p>{{ text }}</p>
      </TooltipContent>
    </Tooltip>
  </TooltipProvider>
</template>

<script setup lang="ts">
import {
  TooltipProvider,
  TooltipTrigger,
  Tooltip,
  TooltipContent,
} from './ui/tooltip';

defineProps<{
  text: string;
}>();
</script>
