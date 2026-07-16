<template>
  <Field :data-invalid="!!errorMessage">
    <FieldLabel
      v-if="label"
      :for="name"
      >{{ label }}</FieldLabel
    >
    <Select
      :id="selectId"
      v-model="model"
      :name="name"
      :aria-invalid="!!errorMessage"
      :default-value="defaultValue"
    >
      <SelectTrigger>
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
import { Field, FieldLabel, FieldError } from '@/components/ui/field';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
  name: HTMLInputElement['name'];
  id?: HTMLInputElement['id'];
  label?: string;
  placeholder: string;
  options: { label: string; value: string | number }[];
  defaultValue?: string | number;
  errorMessage?: string;
}>();

const selectId = computed(() => props.id ?? useId());

const model = defineModel<string | number>();
</script>
