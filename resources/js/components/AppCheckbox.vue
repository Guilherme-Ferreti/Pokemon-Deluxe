<template>
  <Field
    orientation="horizontal"
    :data-invalid="!!errorMessage"
  >
    <Checkbox
      v-model="model"
      :id="id"
      :name="name"
      :value="1"
      :required="required"
      :aria-invalid="!!errorMessage"
      @keydown.enter.prevent="() => (model = !model)"
    />
    <FieldLabel for="remember">{{ label }}</FieldLabel>
    <FieldError v-if="errorMessage">{{ errorMessage }}</FieldError>
  </Field>
</template>

<script setup lang="ts">
import { Checkbox } from './ui/checkbox';
import { Field, FieldError, FieldLabel } from './ui/field';

defineProps<{
  label: string;
  id: HTMLInputElement['id'];
  name: HTMLInputElement['name'];
  required?: boolean;
  errorMessage?: string;
}>();

const model = defineModel<boolean>();
</script>
