<template>
  <Field :data-invalid="!!errorMessage">
    <FieldLabel :for="name">{{ label }}</FieldLabel>
    <Input
      :id="name"
      :type="type"
      :name="name"
      :default-value="value"
      :placeholder="placeholder"
      :required="required"
      :aria-invalid="!!errorMessage"
      @blur="$emit('blur')"
    />
    <FieldError v-if="errorMessage">{{ errorMessage }}</FieldError>
  </Field>
</template>

<script setup lang="ts">
import { Field, FieldError, FieldLabel } from './ui/field';
import { Input } from './ui/input';

withDefaults(
  defineProps<{
    label: string;
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
