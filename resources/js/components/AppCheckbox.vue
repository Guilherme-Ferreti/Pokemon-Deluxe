<template>
  <Field
    orientation="horizontal"
    class="gap-2"
    :data-invalid="!!errorMessage"
  >
    <Checkbox
      :id="inputId"
      v-model="inputValue"
      :name="name"
      :value="value"
      :required="required"
      :aria-invalid="!!errorMessage"
      @keydown.enter.prevent="$event.element.click()"
    />
    <FieldLabel :for="inputId">{{ label }}</FieldLabel>
    <FieldError v-if="errorMessage">{{ errorMessage }}</FieldError>
  </Field>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue';
import { Checkbox } from './ui/checkbox';
import { Field, FieldError, FieldLabel } from './ui/field';

interface AppCheckboxProps {
  label: string;
  name: HTMLInputElement['name'];
  id?: HTMLInputElement['id'];
  required?: boolean;
  errorMessage?: string;
  value?: string | number;
}

const props = withDefaults(defineProps<AppCheckboxProps>(), {
  value: 1,
});

const inputId = computed(() => props.id ?? useId());

const model = defineModel<boolean | (string | number)[]>({
  default: false,
});

const inputValue = computed({
  get: () =>
    typeof model.value === 'boolean'
      ? model.value
      : model.value.includes(props.value),
  set: (isChecked) => {
    if (typeof model.value === 'boolean') {
      model.value = isChecked;

      return;
    }

    model.value = isChecked
      ? [...model.value, props.value]
      : model.value.filter((item) => item !== props.value);
  },
});
</script>
