<template>
  <Field :data-invalid="!!errorMessage">
    <FieldLabel
      v-if="label"
      :for="name"
      >{{ label }}</FieldLabel
    >
    <InputGroup>
      <InputGroupInput
        :id="name"
        :type="type"
        :name="name"
        :default-value="value"
        :placeholder="placeholder"
        :required="required"
        :aria-invalid="!!errorMessage"
        @blur="$emit('blur')"
      />
      <InputGroupAddon v-if="icon">
        <component :is="icon" />
      </InputGroupAddon>
    </InputGroup>
    <FieldError v-if="errorMessage">{{ errorMessage }}</FieldError>
  </Field>
</template>

<script setup lang="ts">
import type { Icon } from '@tabler/icons-vue';
import { Field, FieldError, FieldLabel } from './ui/field';
import { InputGroup, InputGroupAddon, InputGroupInput } from './ui/input-group';

withDefaults(
  defineProps<{
    label?: string;
    icon?: Icon;
    type: HTMLInputElement['type'];
    name: HTMLInputElement['name'];
    required?: boolean;
    placeholder?: HTMLInputElement['placeholder'];
    value?: HTMLInputElement['value'];
    errorMessage?: string;
  }>(),
  {
    required: false,
  },
);

defineEmits<{
  (event: 'blur'): void;
}>();
</script>
