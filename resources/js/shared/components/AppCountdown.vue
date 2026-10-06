<template>
  <div class="flex flex-col items-center gap-2">
    <span class="flex items-center gap-1 text-xs text-muted-foreground">
      <IconClock class="size-4" />
      <span class="w-[12ch]">
        <WhenMounted>
          {{ days }}d {{ hours }}h {{ minutes }}m {{ seconds }}s
          <template #fallback>Calculating...</template>
        </WhenMounted>
      </span>
    </span>
    <div class="flex w-full items-center justify-between gap-2">
      <Progress :model-value="progress" />
      <span class="w-[3ch] text-center text-xs text-muted-foreground">
        {{ Math.floor(progress) }}%
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { WhenMounted } from '@inertiajs/vue3';
import { IconClock } from '@tabler/icons-vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Progress } from '@/shared/components/ui/progress';

const props = defineProps<{
  startDate: Date;
  endDate: Date;
}>();

const emit = defineEmits<{
  complete: [void];
}>();

let interval: number;

const milisecondsInDay = 86_400_000;
const milisecondsInHour = 3_600_000;
const milisecondsInMinute = 60_000;
const milisecondsInSecond = 1_000;

const now = ref<number>(0);

const start = computed(() => props.startDate.getTime());
const end = computed(() => props.endDate.getTime());

const remaining = computed(() => Math.max(end.value - now.value, 0));

const days = computed(() => Math.floor(remaining.value / milisecondsInDay));

const hours = computed(() =>
  Math.floor((remaining.value % milisecondsInDay) / milisecondsInHour),
);

const minutes = computed(() =>
  Math.floor((remaining.value % milisecondsInHour) / milisecondsInMinute),
);

const seconds = computed(() =>
  Math.floor((remaining.value % milisecondsInMinute) / milisecondsInSecond),
);

const progress = computed(() => {
  const duration = end.value - start.value;

  if (duration <= 0) {
    return 100;
  }

  const elapsed = now.value - start.value;

  return Math.min(100, Math.max(0, (elapsed / duration) * 100));
});

onMounted(() => {
  now.value = Date.now();

  interval = setInterval(() => {
    now.value = Date.now();
  }, milisecondsInSecond);
});

onUnmounted(() => clearInterval(interval));

watch(progress, () => {
  if (progress.value === 100) {
    clearInterval(interval);

    emit('complete');
  }
});
</script>
