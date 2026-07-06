<template>
  <Field :data-invalid="!!errorMessage">
    <FieldLabel :for="name">{{ label }}</FieldLabel>
    <Input
      :id="name"
      :type="type"
      :name="name"
      :placeholder="placeholder"
      :required="required"
      :aria-invalid="!!errorMessage"
    />
    <FieldError v-if="errorMessage">{{ errorMessage }}</FieldError>
  </Field>
</template>

<script setup lang="ts">
import Field from './ui/field/Field.vue';
import FieldError from './ui/field/FieldError.vue';
import FieldLabel from './ui/field/FieldLabel.vue';
import Input from './ui/input/Input.vue';

withDefaults(
  defineProps<{
    label: string;
    type: HTMLInputElement['type'];
    name: HTMLInputElement['name'];
    required?: boolean;
    placeholder?: HTMLInputElement['placeholder'];
    errorMessage?: string;
  }>(),
  {
    required: false,
  },
);
</script>
