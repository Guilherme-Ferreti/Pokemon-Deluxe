<template>
  <Field :data-invalid="!!errorMessage">
    <FieldLabel
      v-if="label"
      :for="inputId"
    >
      {{ label }}
    </FieldLabel>
    <InputGroup>
      <InputGroupInput
        :id="inputId"
        v-model="model"
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
    <FieldError
      v-if="errorMessage"
      data-test="error-message"
    >
      {{ errorMessage }}
    </FieldError>
  </Field>
</template>

<script setup lang="ts">
import type { Icon } from '@tabler/icons-vue';
import { computed, useId } from 'vue';
import { Field, FieldError, FieldLabel } from '@/shared/components/ui/field';
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
} from '@/shared/components/ui/input-group';

interface AppInputProps {
  label?: string;
  icon?: Icon;
  type: HTMLInputElement['type'];
  name: HTMLInputElement['name'];
  id?: HTMLInputElement['id'];
  required?: boolean;
  placeholder?: HTMLInputElement['placeholder'];
  value?: HTMLInputElement['value'];
  errorMessage?: string;
}

const props = withDefaults(defineProps<AppInputProps>(), {
  required: false,
});

const generatedId = useId();

const inputId = computed(() => props.id ?? generatedId);

const model = defineModel<string>();

defineEmits<{
  blur: [];
}>();
</script>
