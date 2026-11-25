<template>
  <Dialog
    :open="item !== null"
    @update:open="(isOpen) => !isOpen && emit('close')"
  >
    <DialogContent
      v-if="item"
      data-test="inventory-item-dialog"
    >
      <DialogHeader class="flex-row items-start gap-4 text-left">
        <ItemSprite
          class="size-16 shrink-0"
          :name="item.name"
          :url="item.sprite"
        />
        <div class="flex min-w-0 flex-col gap-1">
          <DialogTitle>{{ item.name }}</DialogTitle>
          <DialogDescription>{{ item.description }}</DialogDescription>
          <p class="text-sm tabular-nums">
            You own
            <span class="font-semibold">
              {{ item.quantity.toLocaleString('en-US') }}
            </span>
          </p>
        </div>
      </DialogHeader>
      <DialogFooter>
        <DialogClose as-child>
          <Button variant="ghost">Close</Button>
        </DialogClose>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import ItemSprite from '@/shared/components/game/ItemSprite.vue';
import { Button } from '@/shared/components/ui/button';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/shared/components/ui/dialog';
import type { OwnedItem } from '../../../types/page-props/InventoryIndexPageProps';

defineProps<{
  item: OwnedItem | null;
}>();

const emit = defineEmits<{
  close: [];
}>();
</script>
