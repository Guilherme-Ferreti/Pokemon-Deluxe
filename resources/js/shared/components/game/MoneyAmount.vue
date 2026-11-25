<template>
  <span class="tabular-nums">{{ formattedAmount }}</span>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { formatMoney } from '@/shared/lib/utils';

interface MoneyAmountProps {
  amount: number;
}

const props = defineProps<MoneyAmountProps>();

const formattedAmount = computed(() => formatMoney(props.amount));
</script>
