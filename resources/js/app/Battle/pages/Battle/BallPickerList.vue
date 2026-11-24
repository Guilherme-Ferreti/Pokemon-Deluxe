<template>
  <div
    class="ball-picker-list"
    role="list"
    aria-label="Choose a ball"
    data-test="ball-picker-list"
  >
    <button
      v-for="pokeball in page.props.pokeballs"
      :key="pokeball.slug"
      type="button"
      role="listitem"
      class="ball-picker-list__ball"
      :data-test="`ball-picker-list__ball-${pokeball.slug}-button`"
      :disabled="form.processing"
      @click="throwBall(pokeball.slug)"
    >
      <ItemSprite
        v-if="pokeball.sprite"
        class="ball-picker-list__sprite"
        :name="pokeball.name"
        :url="pokeball.sprite"
      />
      <span class="ball-picker-list__name">
        {{ pokeball.name }}
        <Badge
          v-if="pokeball.isBoosted"
          variant="outline"
          class="ball-picker-list__boosted"
        >
          Boosted
        </Badge>
      </span>
      <IconLoader2
        v-if="form.processing && form.pokeball === pokeball.slug"
        class="ball-picker-list__spinner"
        aria-label="Throwing"
      />
      <strong class="ball-picker-list__multiplier">
        ×{{ pokeball.multiplier }}
      </strong>
      <span class="ball-picker-list__quantity">
        ×{{ pokeball.quantity.toLocaleString('en-US') }}
      </span>
    </button>
    <p
      v-if="page.props.pokeballs.length === 0"
      class="ball-picker-list__empty"
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

<style scoped>
@reference '@css/app.css';

.ball-picker-list {
  @apply -mr-2 flex max-h-80 flex-col gap-0.5 overflow-y-auto overscroll-contain pr-2;
}

.ball-picker-list__ball {
  @apply flex min-h-11 shrink-0 items-center gap-2 rounded-lg px-2 text-left text-sm transition-colors hover:bg-muted disabled:pointer-events-none disabled:opacity-60;
}

.ball-picker-list__sprite {
  @apply shrink-0;
}

.ball-picker-list__name {
  @apply flex min-w-0 flex-1 flex-wrap items-center gap-1.5 font-medium;
}

.ball-picker-list__boosted {
  @apply border-green-600/40 bg-green-50 text-green-700 dark:border-green-400/40 dark:bg-green-950 dark:text-green-400;
}

.ball-picker-list__spinner {
  @apply size-4 animate-spin text-muted-foreground;
}

.ball-picker-list__multiplier {
  @apply tabular-nums;
}

.ball-picker-list__quantity {
  @apply w-12 text-right text-xs text-muted-foreground tabular-nums;
}

.ball-picker-list__empty {
  @apply rounded-lg bg-muted p-3 text-sm text-muted-foreground;
}
</style>
