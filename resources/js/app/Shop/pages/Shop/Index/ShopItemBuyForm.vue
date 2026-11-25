<template>
  <div class="flex flex-wrap items-center justify-center gap-2">
    <InputGroup class="w-32">
      <InputGroupAddon align="inline-start">
        <InputGroupButton
          size="icon-xs"
          aria-label="Fewer"
          :data-test="`shop-item-${item.id}-buy-form__fewer-button`"
          :disabled="quantity <= 1"
          @click="setQuantity(quantity - 1)"
        >
          <IconMinus />
        </InputGroupButton>
      </InputGroupAddon>
      <InputGroupInput
        type="number"
        class="text-center"
        min="1"
        :max="maxQuantity"
        :aria-label="`Quantity of ${item.name}`"
        :data-test="`shop-item-${item.id}-buy-form__quantity-input`"
        :model-value="quantity"
        @update:model-value="setQuantity(Number($event))"
      />
      <InputGroupAddon align="inline-end">
        <InputGroupButton
          size="icon-xs"
          aria-label="More"
          :data-test="`shop-item-${item.id}-buy-form__more-button`"
          :disabled="quantity >= maxQuantity"
          @click="setQuantity(quantity + 1)"
        >
          <IconPlus />
        </InputGroupButton>
      </InputGroupAddon>
    </InputGroup>
    <div class="flex flex-col items-end gap-0.5">
      <Button
        type="button"
        variant="outline"
        class="w-44"
        :data-test="`shop-item-${item.id}-buy-form__buy-button`"
        :disabled="form.processing || isStackFull || !canAfford"
        @click="handleBuy"
      >
        Buy · <MoneyAmount :amount="totalPrice" />
      </Button>
      <span
        v-if="isStackFull"
        class="text-xs text-destructive"
        :data-test="`shop-item-${item.id}-buy-form__stack-full-indicator`"
      >
        Your stack is full
      </span>
    </div>
    <AlertDialog v-model:open="isConfirming">
      <AlertDialogContent
        :data-test="`shop-item-${item.id}-buy-form__confirm-dialog`"
      >
        <AlertDialogHeader>
          <AlertDialogTitle>
            Buy {{ quantityLabel }} for {{ formatMoney(totalPrice) }}?
          </AlertDialogTitle>
          <AlertDialogDescription>
            You'll have {{ formatMoney(money - totalPrice) }} left.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Not now</AlertDialogCancel>
          <AlertDialogAction
            :data-test="`shop-item-${item.id}-buy-form__confirm-button`"
            :disabled="form.processing"
            @click.prevent="buy"
          >
            Buy
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconMinus, IconPlus } from '@tabler/icons-vue';
import { computed, ref } from 'vue';
import MoneyAmount from '@/shared/components/game/MoneyAmount.vue';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/shared/components/ui/alert-dialog';
import { Button } from '@/shared/components/ui/button';
import {
  InputGroup,
  InputGroupAddon,
  InputGroupButton,
  InputGroupInput,
} from '@/shared/components/ui/input-group';
import { formatMoney } from '@/shared/lib/utils';
import { route } from '@/shared/types/helpers/route';
import type {
  BuyableItem,
  ShopIndexPageProps,
} from '../../../types/page-props/ShopIndexPageProps';

const props = defineProps<{
  item: BuyableItem;
}>();

const page = usePage<ShopIndexPageProps>();

const quantity = ref(1);

const isConfirming = ref(false);

const money = computed(() => page.props.auth.user?.money ?? 0);

const roomInStack = computed(
  () => page.props.maxStack - props.item.ownedQuantity,
);

const maxQuantity = computed(() => Math.max(1, roomInStack.value));

const totalPrice = computed(() => quantity.value * props.item.price);

const quantityLabel = computed(
  () =>
    `${quantity.value.toLocaleString('en-US')} ${quantity.value === 1 ? props.item.name : props.item.pluralName}`,
);

const isStackFull = computed(() => roomInStack.value < 1);

const canAfford = computed(() => totalPrice.value <= money.value);

const form = useForm({
  item_type: props.item.itemType,
  item_id: props.item.id,
  quantity: 1,
});

const setQuantity = (value: number) => {
  quantity.value = Math.min(
    maxQuantity.value,
    Math.max(1, Math.floor(value) || 1),
  );
};

const handleBuy = () => {
  if (totalPrice.value > page.props.confirmPurchaseOver) {
    isConfirming.value = true;

    return;
  }

  buy();
};

const buy = () => {
  form.quantity = quantity.value;

  form.post(route('shop.web.buy'), {
    preserveScroll: true,
    onSuccess: () => {
      quantity.value = 1;
      isConfirming.value = false;
    },
  });
};
</script>
