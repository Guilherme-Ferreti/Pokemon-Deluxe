<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import type { ButtonVariants } from '@/shared/components/ui/button';
import { Button } from '@/shared/components/ui/button';
import type { InputGroupButtonVariants } from '@/shared/components/ui/input-group';
import { inputGroupButtonVariants } from '@/shared/components/ui/input-group';
import { cn } from '@/shared/lib/utils';

interface InputGroupButtonProps {
  variant?: ButtonVariants['variant'];
  size?: InputGroupButtonVariants['size'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<InputGroupButtonProps>(), {
  size: 'xs',
  variant: 'ghost',
});
</script>

<template>
  <Button
    :data-size="props.size"
    :variant="props.variant"
    :class="cn(inputGroupButtonVariants({ size: props.size }), props.class)"
  >
    <slot />
  </Button>
</template>
