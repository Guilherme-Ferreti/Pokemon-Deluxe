<template>
  <Field :data-invalid="!!errorMessage">
    <FieldLabel
      v-if="label"
      :for="selectId"
    >
      {{ label }}
    </FieldLabel>
    <Select
      v-model="model"
      :name="name"
      :default-value="defaultValue"
    >
      <SelectTrigger
        :id="selectId"
        :aria-invalid="!!errorMessage"
      >
        <SelectValue :placeholder="placeholder" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          <SelectItem
            v-for="option in options"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </SelectItem>
        </SelectGroup>
      </SelectContent>
    </Select>
    <FieldError v-if="errorMessage">{{ errorMessage }}</FieldError>
  </Field>
</template>

<script setup lang="ts">
import { computed, useId } from 'vue';
import { Field, FieldLabel, FieldError } from '@/shared/components/ui/field';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/shared/components/ui/select';

const props = defineProps<{
  name: HTMLInputElement['name'];
  id?: HTMLInputElement['id'];
  label?: string;
  placeholder: string;
  options: { label: string; value: string | number }[];
  defaultValue?: string | number;
  errorMessage?: string;
}>();

const generatedId = useId();

const selectId = computed(() => props.id ?? generatedId);

const model = defineModel<string | number>();
</script>
