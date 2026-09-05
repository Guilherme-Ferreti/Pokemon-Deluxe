<template>
  <Combobox
    v-model="selectedFramework"
    :ignore-filter="true"
  >
    <ComboboxAnchor as-child>
      <ComboboxInput as-child>
        <InputGroup class="w-full bg-background shadow-xs">
          <InputGroupInput
            v-model="searchInputValue"
            name="search"
            type="search"
            placeholder="Search a pokemon..."
            autocomplete="off"
          />
          <InputGroupAddon>
            <IconSearch />
          </InputGroupAddon>
        </InputGroup>
      </ComboboxInput>
    </ComboboxAnchor>
    <ComboboxList class="w-(--reka-popper-anchor-width)">
      <ComboboxEmpty>No framework found.</ComboboxEmpty>
      <ComboboxGroup>
        <ComboboxItem
          v-for="framework in filteredFrameworks"
          :key="framework.value"
          :value="framework"
          @select="() => (searchInputValue = framework.label)"
        >
          {{ framework.label }}
        </ComboboxItem>
      </ComboboxGroup>
    </ComboboxList>
  </Combobox>
</template>

<script setup lang="ts">
import { IconSearch } from '@tabler/icons-vue';
import { ComboboxInput } from 'reka-ui';
import { computed, ref } from 'vue';
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxItem,
  ComboboxList,
} from '@/components/ui/combobox';
import InputGroup from '@/components/ui/input-group/InputGroup.vue';
import InputGroupAddon from '@/components/ui/input-group/InputGroupAddon.vue';
import InputGroupInput from '@/components/ui/input-group/InputGroupInput.vue';

const frameworks = [
  { value: 'next.js', label: 'Next.js' },
  { value: 'sveltekit', label: 'SvelteKit' },
  { value: 'nuxt.js', label: 'Nuxt.js' },
  { value: 'remix', label: 'Remix' },
  { value: 'astro', label: 'Astro' },
];

const filteredFrameworks = computed(() =>
  frameworks.filter((framework) =>
    framework.label.toLowerCase().includes(searchInputValue.value),
  ),
);

const selectedFramework = ref<(typeof frameworks)[number]>();
const searchInputValue = ref('');
</script>
