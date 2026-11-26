<template>
  <div
    data-slot="choice-card"
    :data-state="isChecked ? 'checked' : 'unchecked'"
    :data-disabled="isDisabled ? '' : undefined"
    :class="
      cn(
        'relative flex items-stretch rounded-lg border border-border bg-card transition-colors duration-200 motion-reduce:transition-none',
        'has-[[role=radio]:focus-visible]:ring-3 has-[[role=radio]:focus-visible]:ring-ring/50',
        'data-[state=unchecked]:not-data-disabled:hover:bg-muted',
        'data-[state=checked]:bg-muted data-[state=checked]:ring-1 data-[state=checked]:ring-primary dark:data-[state=checked]:bg-muted',
        'data-disabled:opacity-50',
        invalid && 'border-destructive',
        props.class,
      )
    "
  >
    <div
      v-if="$slots.leading"
      class="flex shrink-0 items-center pl-2"
    >
      <slot name="leading" />
    </div>
    <Label
      :for="id"
      :class="
        cn(
          'flex min-h-10 min-w-0 flex-1 cursor-pointer items-center gap-3 p-3 leading-snug font-normal',
          isDisabled && 'cursor-not-allowed',
          $slots.leading && 'pl-2',
        )
      "
    >
      <RadioGroupItem
        :id="id"
        class="sr-only"
        :value="value"
        :disabled="disabled"
        :aria-invalid="invalid || undefined"
      />
      <slot />
    </Label>
  </div>
</template>

<script setup lang="ts">
import { injectRadioGroupRootContext } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { computed, useId } from 'vue';
import { Label } from '@/shared/components/ui/label';
import { RadioGroupItem } from '@/shared/components/ui/radio-group';
import { cn } from '@/shared/lib/utils';

const props = withDefaults(
  defineProps<{
    value: string;
    disabled?: boolean;
    invalid?: boolean;
    class?: HTMLAttributes['class'];
  }>(),
  {
    disabled: false,
    invalid: false,
  },
);

const id = useId();

const radioGroup = injectRadioGroupRootContext();

const isChecked = computed(() => radioGroup.modelValue?.value === props.value);

const isDisabled = computed(() => props.disabled || radioGroup.disabled.value);
</script>
