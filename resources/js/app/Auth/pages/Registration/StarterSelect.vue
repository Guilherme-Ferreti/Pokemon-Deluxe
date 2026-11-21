<template>
  <Field
    data-test="starter-select"
    :data-invalid="!!errorMessage"
  >
    <FieldLabel :for="selectId">Choose your starter</FieldLabel>
    <Select name="starter">
      <SelectTrigger
        :id="selectId"
        class="w-full"
        data-test="starter-select__starter-select"
        :aria-invalid="!!errorMessage"
      >
        <SelectValue placeholder="Choose a starter" />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup
          v-for="region in page.props.starterRegions"
          :key="region.name"
        >
          <SelectLabel>{{ region.name }}</SelectLabel>
          <SelectItem
            v-for="starter in region.starters"
            :key="starter.slug"
            :value="starter.slug"
            :data-test="`starter-select__starter-${starter.slug}-item`"
          >
            <SpeciesPortrait
              class="size-6"
              aria-hidden="true"
              :url="starter.portrait"
              :name="starter.name"
            />
            {{ starter.name }}
          </SelectItem>
        </SelectGroup>
      </SelectContent>
    </Select>
    <FieldError v-if="errorMessage">{{ errorMessage }}</FieldError>
  </Field>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useId } from 'vue';
import SpeciesPortrait from '@/shared/components/game/SpeciesPortrait.vue';
import { Field, FieldError, FieldLabel } from '@/shared/components/ui/field';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/shared/components/ui/select';
import type { RegistrationPageProps } from '../../types/page-props/RegistrationPageProps';

defineProps<{
  errorMessage?: string;
}>();

const page = usePage<RegistrationPageProps>();

const selectId = useId();
</script>
