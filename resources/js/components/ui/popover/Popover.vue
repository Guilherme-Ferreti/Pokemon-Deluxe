<script setup lang="ts">
import type { PopoverRootEmits, PopoverRootProps } from 'reka-ui';
import { PopoverRoot, useForwardPropsEmits } from 'reka-ui';

interface Emits extends PopoverRootEmits {
  open: [];
  close: [];
}

const props = defineProps<PopoverRootProps>();
const emits = defineEmits<Emits>();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
  <PopoverRoot
    v-slot="slotProps"
    data-slot="popover"
    v-bind="forwarded"
    @update:open="(open) => (open ? $emit('open') : $emit('close'))"
  >
    <slot v-bind="slotProps" />
  </PopoverRoot>
</template>
