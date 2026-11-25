<template>
  <IncubatorCard
    :data-test="`incubator-${index}-card--not-purchased`"
    :index="index"
    :badge="{ icon: IconLock, text: 'Not purchased' }"
  >
    <template #picture>
      <div
        class="mx-auto flex size-12 items-center justify-center rounded-full border-2 border-dashed border-muted-foreground/40"
      >
        <IconLock class="size-6 text-muted-foreground/70" />
      </div>
    </template>
    <p class="text-center text-sm text-muted-foreground">
      This incubator has not been purchased yet
    </p>
    <template #footer>
      <div class="flex flex-col items-center gap-1">
        <span
          class="mb-2 text-sm"
          :data-test="`incubator-${index}-card--not-purchased__price-indicator`"
        >
          <span
            v-if="price === 0"
            class="text-sm text-muted-foreground"
          >
            Free
          </span>
          <MoneyAmount
            v-else
            class="text-xs font-medium"
            :amount="price"
          />
        </span>
        <Button
          type="button"
          variant="outline"
          size="sm"
          :data-test="`incubator-${index}-card--not-purchased__purchase-button`"
          :disabled="form.processing || !canAfford"
          @click="handlePurchase"
        >
          Purchase
        </Button>
      </div>
    </template>
  </IncubatorCard>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconLock } from '@tabler/icons-vue';
import { computed } from 'vue';
import MoneyAmount from '@/shared/components/game/MoneyAmount.vue';
import { Button } from '@/shared/components/ui/button';
import { route } from '@/shared/types/helpers/route';
import type {
  Incubator,
  NurseryPageProps,
} from '../../types/page-props/NurseryPageProps';
import IncubatorCard from './IncubatorCard.vue';

const props = defineProps<{
  index: Incubator['index'];
}>();

const page = usePage<NurseryPageProps>();

const price = computed(() => page.props.nextIncubatorPrice);

const money = computed(() => page.props.auth.user?.money ?? 0);

const canAfford = computed(() => money.value >= price.value);

const form = useForm();

function handlePurchase() {
  form.post(
    route('pokemon.web.incubators.purchase', {
      incubatorIndex: props.index,
    }),
    {
      preserveScroll: true,
      only: ['incubators', 'nextIncubatorPrice', 'auth'],
    },
  );
}
</script>
