<template>
  <Dialog @close="$emit('close')">
    <Tooltip>
      <AppIndicator
        data-test="search-filters-dialog__active-filters-indicator"
        :show="activeFiltersCount > 0"
        :text="activeFiltersCount.toString()"
      >
        <TooltipTrigger as-child>
          <DialogTrigger as-child>
            <Button
              type="button"
              variant="outline"
              size="icon"
              aria-label="Filters"
              data-test="search-filters-dialog__open-filters-button"
            >
              <IconFilter2 />
            </Button>
          </DialogTrigger>
        </TooltipTrigger>
      </AppIndicator>
      <TooltipContent>{{ tooltipText }}</TooltipContent>
      <DialogContent class="max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{{ dialogTitle }}</DialogTitle>
          <DialogDescription>
            {{ dialogDescription }}
          </DialogDescription>
        </DialogHeader>
        <div class="flex flex-col gap-6">
          <slot />
        </div>
        <DialogFooter>
          <DialogClose as-child>
            <Button
              type="button"
              variant="outline"
              data-test="search-filters-dialog__reset-filters-button"
              @click="$emit('reset')"
            >
              Reset
            </Button>
          </DialogClose>
          <DialogClose as-child>
            <Button
              type="button"
              variant="outline"
              class="sm:ml-auto"
              data-test="search-filters-dialog__close-filters-button"
            >
              Cancel
            </Button>
          </DialogClose>
          <DialogClose as-child>
            <Button
              type="button"
              data-test="search-filters-dialog__apply-filters-button"
              @click="$emit('apply')"
            >
              Apply
            </Button>
          </DialogClose>
        </DialogFooter>
      </DialogContent>
    </Tooltip>
  </Dialog>
</template>

<script setup lang="ts">
import { IconFilter2 } from '@tabler/icons-vue';
import AppIndicator from '@/shared/components/AppIndicator.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogFooter,
  DialogTitle,
  DialogDescription,
  DialogClose,
} from '@/shared/components/ui/dialog';
import {
  Tooltip,
  TooltipTrigger,
  TooltipContent,
} from '@/shared/components/ui/tooltip';

defineProps<{
  activeFiltersCount: number;
  tooltipText: string;
  dialogTitle: string;
  dialogDescription: string;
}>();

defineEmits<{
  close: [value: void];
  apply: [value: void];
  reset: [value: void];
}>();
</script>
