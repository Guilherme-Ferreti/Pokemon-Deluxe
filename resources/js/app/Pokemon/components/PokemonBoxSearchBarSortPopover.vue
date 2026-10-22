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
          :options="page.props.filters.options.sort"
        />
        <AppSelect
          v-model="selectedDir"
          name="dir"
          label="Direction"
          placeholder="Choose a value"
          :options="page.props.filters.options.dir"
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
import { usePage } from '@inertiajs/vue3';
import { IconArrowsDownUp } from '@tabler/icons-vue';
import { PopoverClose } from 'reka-ui';
import { SearchBoxSortBy } from '@/app/Pokemon/types/enums/SearchBoxSortBy';
import type { BoxIndexPageProps } from '@/app/Pokemon/types/page-props/BoxIndexPageProps';
import AppSelect from '@/shared/components/AppSelect.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/shared/components/ui/popover';
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/shared/components/ui/tooltip';
import { SortDirection } from '@/shared/types/enums';

const page = usePage<BoxIndexPageProps>();

const selectedSort = defineModel<
  BoxIndexPageProps['filters']['active']['sort']
>('sort', {
  default: SearchBoxSortBy.CATCH_DATE,
});

const selectedDir = defineModel<BoxIndexPageProps['filters']['active']['dir']>(
  'dir',
  {
    default: SortDirection.DESC,
  },
);

defineEmits<{
  submit: [value: void];
  close: [value: void];
}>();

function clearFilters() {
  selectedSort.value = SearchBoxSortBy.CATCH_DATE;
  selectedDir.value = SortDirection.DESC;
}
</script>
