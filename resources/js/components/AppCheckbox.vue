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
import Checkbox from './ui/checkbox/Checkbox.vue';
import Field from './ui/field/Field.vue';
import FieldError from './ui/field/FieldError.vue';
import FieldLabel from './ui/field/FieldLabel.vue';

defineProps<{
  label: string;
  id: HTMLInputElement['id'];
  name: HTMLInputElement['name'];
  required?: boolean;
  errorMessage?: string;
}>();

const model = defineModel<boolean>();
</script>
