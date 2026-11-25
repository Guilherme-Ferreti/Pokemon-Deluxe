<template>
  <div
    class="-mr-2 flex max-h-80 flex-col gap-0.5 overflow-y-auto overscroll-contain pr-2"
    role="list"
    aria-label="Choose a ball"
    data-test="ball-picker-list"
  >
    <button
      v-for="pokeball in page.props.pokeballs"
      :key="pokeball.slug"
      type="button"
      role="listitem"
      class="flex min-h-11 shrink-0 items-center gap-2 rounded-lg px-2 text-left text-sm transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-60"
      :data-test="`ball-picker-list__ball-${pokeball.slug}-button`"
      :disabled="form.processing"
      @click="throwBall(pokeball.slug)"
    >
      <ItemSprite
        class="shrink-0"
        :name="pokeball.name"
        :url="pokeball.sprite"
      />
      <span
        class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5 font-medium"
      >
        {{ pokeball.name }}
        <Badge
          v-if="pokeball.isBoosted"
          variant="outline"
          class="border-green-600/40 bg-green-50 text-green-700 dark:border-green-400/40 dark:bg-green-950 dark:text-green-400"
        >
          Boosted
        </Badge>
      </span>
      <IconLoader2
        v-if="form.processing && form.pokeball === pokeball.slug"
        class="size-4 animate-spin text-muted-foreground"
        aria-label="Throwing"
      />
      <strong class="tabular-nums"> x{{ pokeball.multiplier }} </strong>
      <span class="w-12 text-right text-xs text-muted-foreground tabular-nums">
        x{{ pokeball.quantity.toLocaleString('en-US') }}
      </span>
    </button>
    <p
      v-if="page.props.pokeballs.length === 0"
      class="rounded-lg bg-muted p-3 text-sm text-muted-foreground"
      data-test="ball-picker-list__empty-state"
    >
      You have no balls left.
    </p>
  </div>
</template>

<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { IconLoader2 } from '@tabler/icons-vue';
import ItemSprite from '@/shared/components/game/ItemSprite.vue';
import { Badge } from '@/shared/components/ui/badge';
import { route } from '@/shared/types/helpers/route';
import type { BattleShowPageProps } from '../../types/page-props/BattleShowPageProps';

const emit = defineEmits<{
  thrown: [];
}>();

const page = usePage<BattleShowPageProps>();

const form = useForm<{ pokeball: string | null }>({ pokeball: null });

function throwBall(slug: string): void {
  form.pokeball = slug;

  form.post(route('battle.web.throw-ball'), {
    preserveScroll: true,
    onSuccess: () => emit('thrown'),
    onFinish: () => form.reset(),
  });
}
</script>
