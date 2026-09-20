<template>
  <Popover @close="$emit('close')">
    <Tooltip>
      <TooltipTrigger as-child>
        <PopoverTrigger as-child>
          <Button
            type="button"
            variant="outline"
            size="icon"
            aria-label="Sort"
          >
            <IconArrowsDownUp />
          </Button>
        </PopoverTrigger>
      </TooltipTrigger>
      <TooltipContent>Sort</TooltipContent>
      <PopoverContent class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
          <h2 class="text-lg leading-none font-semibold">Sort</h2>
          <p class="text-sm text-muted-foreground">
            Use these options to sort your results.
          </p>
        </div>
        <AppSelect
          v-model="selectedSort"
          name="sort"
          label="Sort by"
          placeholder="Choose a value"
          :options="sortOptions"
        />
        <AppSelect
          v-model="selectedDir"
          name="dir"
          label="Direction"
          placeholder="Choose a value"
          :options="dirOptions"
        />
        <div class="flex">
          <PopoverClose as-child>
            <Button
              type="button"
              variant="outline"
              @click="(clearFilters(), $emit('submit'))"
            >
              Reset
            </Button>
          </PopoverClose>
          <PopoverClose as-child>
            <Button
              type="button"
              class="ml-auto"
              @click="$emit('submit')"
            >
              Apply
            </Button>
          </PopoverClose>
        </div>
      </PopoverContent>
    </Tooltip>
  </Popover>
</template>

<script setup lang="ts">
import { IconArrowsDownUp } from '@tabler/icons-vue';
import { PopoverClose } from 'reka-ui';
import AppSelect from '@/components/AppSelect.vue';
import { Button } from '@/components/ui/button';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip';
import { SearchBoxSortBy, SortDirection } from '@/types';
import type { PokemonBoxIndexPageProps } from '@/types/pageProps/pokemon/BoxIndexPageProps';

defineProps<{
  sortOptions: PokemonBoxIndexPageProps['filterOptions']['sort'];
  dirOptions: PokemonBoxIndexPageProps['filterOptions']['dir'];
}>();

const selectedSort = defineModel<
  PokemonBoxIndexPageProps['activeFilters']['sort']
>('sort', {
  default: SearchBoxSortBy.CAPTURE_DATE,
});

const selectedDir = defineModel<
  PokemonBoxIndexPageProps['activeFilters']['dir']
>('dir', {
  default: SortDirection.DESC,
});

defineEmits<{
  submit: [value: void];
  close: [value: void];
}>();

function clearFilters() {
  selectedSort.value = SearchBoxSortBy.CAPTURE_DATE;
  selectedDir.value = SortDirection.DESC;
}
</script>
