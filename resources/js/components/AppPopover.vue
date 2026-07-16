<template>
  <Popover>
    <PopoverTrigger>
      <slot name="trigger" />
    </PopoverTrigger>
    <PopoverContent>
      <div class="space-y-6">
        <div
          v-if="title || description"
          class="space-y-2"
        >
          <h2
            v-if="title"
            class="text-lg leading-none font-semibold"
          >
            {{ title }}
          </h2>
          <p
            v-if="description"
            class="text-sm text-muted-foreground"
          >
            {{ description }}
          </p>
        </div>
        <slot />
        <div v-if="$slots.footer">
          <slot name="footer" />
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>

<script setup lang="ts">
import { Popover, PopoverContent, PopoverTrigger } from './ui/popover';

defineProps<{
  title?: string;
  description?: string;
}>();
</script>
