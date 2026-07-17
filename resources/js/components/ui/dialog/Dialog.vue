<script setup lang="ts">
import type { DialogRootEmits, DialogRootProps } from 'reka-ui';
import { DialogRoot, useForwardPropsEmits } from 'reka-ui';

interface Emits extends DialogRootEmits {
  open: [];
  close: [];
}

const props = defineProps<DialogRootProps>();
const emits = defineEmits<Emits>();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
  <DialogRoot
    v-slot="slotProps"
    data-slot="dialog"
    v-bind="forwarded"
    @update:open="(open) => (open ? $emit('open') : $emit('close'))"
  >
    <slot v-bind="slotProps" />
  </DialogRoot>
</template>
